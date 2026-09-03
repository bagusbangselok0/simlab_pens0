<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SimpleDatabaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class SignatureVerificationController extends Controller
{
    /**
     * Display a listing of user signatures for admin review.
     */
    public function index(Request $request)
    {
        $title = 'Verifikasi Tanda Tangan Digital';

        if ($request->ajax()) {
            $query = User::with(['role', 'jabatan', 'verifier'])
                ->where(function ($query) {
                    $query->whereNotNull('signature_path')
                        ->orWhereIn('signature_status', ['pending', 'approved', 'rejected']);
                });

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('signature_status', $request->status);
            }

            if ($request->filled('role_id')) {
                $query->where('role_id', $request->role_id);
            }

            $users = $query->orderByRaw("CASE WHEN signature_status = 'pending' THEN 1 WHEN signature_status = 'rejected' THEN 2 ELSE 3 END")
                ->orderByDesc('updated_at')
                ->get();

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('user_info', function ($row) {
                    $identifier = $row->nrp ? "NRP: {$row->nrp}" : ($row->nip ? "NIP: {$row->nip}" : $row->email);
                    $roleBadge = $row->role ? "<span class='badge bg-light-primary text-primary'>{$row->role->nama_role}</span>" : '';
                    return "<div class='fw-bold'>{$row->full_name}</div><small class='text-muted'>{$identifier}</small> <div>{$roleBadge}</div>";
                })
                ->addColumn('signature_preview', function ($row) {
                    if ($row->signature_path && file_exists(public_path('storage/signatures/' . $row->signature_path))) {
                        $url = asset('storage/signatures/' . $row->signature_path);
                        return "<a href='{$url}' target='_blank'><img src='{$url}' alt='TTD' class='img-thumbnail' style='max-height: 60px; max-width: 120px; object-fit: contain;'></a>";
                    }
                    return "<span class='text-muted fst-italic'>Tidak ada file</span>";
                })
                ->addColumn('status_badge', function ($row) {
                    $badge = "<span class='{$row->signature_status_badge_class}'>{$row->signature_status_label}</span>";
                    if ($row->signature_status === 'rejected' && $row->signature_rejection_note) {
                        $badge .= "<div class='text-danger small mt-1'><strong>Alasan:</strong> " . e($row->signature_rejection_note) . "</div>";
                    } elseif ($row->signature_status === 'approved' && $row->signature_verified_at) {
                        $badge .= "<div class='text-muted small mt-1'>Disetujui: " . $row->signature_verified_at->format('d/m/Y H:i') . "</div>";
                    }
                    return $badge;
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    if ($row->signature_status === 'pending' || $row->signature_status === 'rejected') {
                        $buttons .= "<button type='button' class='btn btn-sm btn-success btn-approve-sign me-1' data-id='{$row->id}' data-name='" . e($row->nama_asli) . "' title='Setujui TTD'><i class='bi bi-check-circle'></i> Setujui</button>";
                    }
                    if ($row->signature_status === 'pending' || $row->signature_status === 'approved') {
                        $buttons .= "<button type='button' class='btn btn-sm btn-danger btn-reject-sign' data-id='{$row->id}' data-name='" . e($row->nama_asli) . "' title='Tolak TTD'><i class='bi bi-x-circle'></i> Tolak</button>";
                    }
                    return $buttons ?: '<span class="text-muted">-</span>';
                })
                ->rawColumns(['user_info', 'signature_preview', 'status_badge', 'action'])
                ->make(true);
        }

        $stats = [
            'pending' => User::where('signature_status', 'pending')->count(),
            'approved' => User::where('signature_status', 'approved')->count(),
            'rejected' => User::where('signature_status', 'rejected')->count(),
            'total' => User::where(function ($query) {
                $query->whereNotNull('signature_path')
                    ->orWhereIn('signature_status', ['pending', 'approved', 'rejected']);
            })->count(),
        ];

        return view('pages.admin.signature_verification.index', compact('title', 'stats'));
    }

    /**
     * Approve user signature.
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        if (!$user->signature_path || !file_exists(public_path('storage/signatures/' . $user->signature_path))) {
            return response()->json([
                'success' => false,
                'message' => 'File tanda tangan fisik tidak ditemukan atau belum diupload.'
            ], 422);
        }

        $user->update([
            'signature_status' => 'approved',
            'signature_rejection_note' => null,
            'signature_verified_at' => now(),
            'signature_verified_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tanda tangan {$user->nama_asli} berhasil disetujui.",
        ]);
    }

    /**
     * Reject user signature and delete physical file.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_note' => 'required|string|max:500',
        ]);

        $user = User::findOrFail($id);

        // Hapus file fisik gambar tanda tangan dari storage jika ada
        if ($user->signature_path && file_exists(public_path('storage/signatures/' . $user->signature_path))) {
            unlink(public_path('storage/signatures/' . $user->signature_path));
        }

        $user->update([
            'signature_path' => null,
            'signature_status' => 'rejected',
            'signature_rejection_note' => $request->rejection_note,
            'signature_verified_at' => now(),
            'signature_verified_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tanda tangan {$user->nama_asli} ditolak dan file fisik telah dihapus oleh sistem.",
        ]);
    }
}
