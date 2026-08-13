<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanLab;
use App\Notifications\PeminjamanNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ApprovalController extends Controller
{
    public function index()
    {
        $title = 'Daftar Persetujuan Peminjaman Lab';

        if (request()->ajax()) {
            $user = Auth::user();
            $jabatanId = $user->jabatan_id;

            // Filter berdasarkan jabatan dan status
            $query = PeminjamanLab::with('lab', 'mahasiswa', 'labManager');

            if ($jabatanId == 4) { // PLP
                $query->whereIn('status', ['pending_plp', 'pending_kalab', 'disetujui', 'kadaluarsa', 'ditolak'])->orderBy('status', 'asc');
            } elseif ($jabatanId == 3) { // Kalab
                $query->whereIn('status', ['pending_kalab', 'pending_plp', 'disetujui', 'kadaluarsa', 'ditolak'])->orderBy('status', 'asc');
            } else {
                // Admin atau jabatan lain bisa lihat semua
                $query->whereIn('status', ['pending_plp', 'pending_kalab', 'disetujui', 'kadaluarsa', 'ditolak'])->orderBy('status', 'asc');
            }

            // Filter berdasarkan status yang dipilih
            $statusFilter = request('status');
            if ($statusFilter && $statusFilter !== 'all') {
                if ($statusFilter === 'pending') {
                    $query->whereIn('status', ['pending_plp', 'pending_kalab']);
                } elseif ($statusFilter === 'approved') {
                    $query->where('status', 'disetujui');
                } elseif ($statusFilter === 'rejected') {
                    $query->where('status', 'ditolak');
                }
            }

            $peminjaman = $query->orderBy('created_at', 'desc')->get();

            return DataTables::of($peminjaman)
                ->addIndexColumn()
                ->addColumn('peminjam', function ($row) {
                    return $row->mahasiswa->getFullNameAttribute();
                })
                ->addColumn('lab', function ($row) {
                    return $row->lab->nama_lab;
                })
                ->addColumn('tujuan', function ($row) {
                    return $row->tujuan;
                })
                ->addColumn('waktu_mulai', function ($row) {
                    return $row->waktu_mulai;
                })
                ->addColumn('waktu_selesai', function ($row) {
                    return $row->waktu_selesai;
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 'pending_plp') {
                        return '<span class="badge bg-warning">Menunggu PLP</span>';
                    } elseif ($row->status == 'pending_kalab') {
                        return '<span class="badge bg-info">Menunggu Kalab</span>';
                    } elseif ($row->status == 'disetujui') {
                        return '<span class="badge bg-success">Disetujui</span>';
                    } elseif ($row->status == 'ditolak') {
                        return '<span class="badge bg-danger">Ditolak</span>';
                    } elseif ($row->status == 'kadaluarsa') {
                        return '<span class="badge bg-secondary">Kadaluarsa</span>';
                    }
                })
                ->addColumn('action', function ($peminjaman) {
                    $user = Auth::user();
                    $jabatanId = $user->jabatan_id;
                    $button = '';

                    // Logic tombol berdasarkan jabatan dan status
                    if ($jabatanId == 4) { // PLP
                        if ($peminjaman->status == 'pending_plp' && $user->id == $peminjaman->labManager->plp_id) {
                            $button = '<button class="btn btn-sm btn-success approve-btn" data-id="' . $peminjaman->id . '">Setuju</button>';
                            $button .= '&nbsp;';
                            $button .= '<button class="btn btn-sm btn-danger reject-btn" data-id="' . $peminjaman->id . '">Tolak</button>';
                        } elseif ($peminjaman->status == 'pending_kalab') {
                            $button = '<span class="text-muted">Menunggu Kalab</span>';
                        }
                    } elseif ($jabatanId == 3) { // Kalab
                        if ($peminjaman->status == 'pending_kalab' && $user->id == $peminjaman->labManager->kalab_id) {
                            $button = '<button class="btn btn-sm btn-success approve-btn" data-id="' . $peminjaman->id . '">Setuju</button>';
                            $button .= '&nbsp;';
                            $button .= '<button class="btn btn-sm btn-danger reject-btn" data-id="' . $peminjaman->id . '">Tolak</button>';
                        }
                    } else {
                        // Admin atau jabatan lain
                        // if ($peminjaman->status == 'pending_plp' || $peminjaman->status == 'pending_kalab') {
                        //     $button = '<button class="btn btn-sm btn-success approve-btn" data-id="' . $peminjaman->id . '">Setuju</button>';
                        //     $button .= '&nbsp;';
                        //     $button .= '<button class="btn btn-sm btn-danger reject-btn" data-id="' . $peminjaman->id . '">Tolak</button>';
                        // } else {
                        $button = '<span class="text-muted">Tidak ada aksi</span>';
                        // }
                    }

                    return $button;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('pages.approval.index', compact('title'));
    }

    public function approve($id)
    {
        $peminjaman = PeminjamanLab::findOrFail($id);
        $user = Auth::user();
        $jabatanId = $user->jabatan_id;

        if ($user->signature_path == null) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum upload tanda tangan. Silahkan upload tanda tangan terlebih dahulu pada halaman profile.'
            ], 422);
        }

        $actionType = null;
        $recipient = null;

        // Logic approval berdasarkan jabatan
        if ($jabatanId == 4) { // PLP
            if ($peminjaman->status == 'pending_plp') {
                $peminjaman->status = 'pending_kalab';
                $peminjaman->tgl_ttd_plp = now(
                    date_default_timezone_set('Asia/Jakarta')
                );
                // simpan signature_path user ke ttd_plp_file
                $peminjaman->ttd_plp_file = $user->signature_path;
                $message = 'Peminjaman disetujui PLP, menunggu approval Kalab';

                $actionType = 'approve_plp';
                $recipient = $peminjaman->labManager->kalab ?? null;
                $recipient->notify(new PeminjamanNotification($peminjaman, $actionType, Auth::user()));
            } else {
                return response()->json(['success' => false, 'message' => 'Status tidak valid untuk approval PLP'], 400);
            }
        } elseif ($jabatanId == 3) { // Kalab
            if ($peminjaman->status == 'pending_kalab') {
                $peminjaman->status = 'disetujui';
                $peminjaman->tgl_ttd_kalab = now(
                    date_default_timezone_set('Asia/Jakarta')
                );
                // simpan signature_path user ke ttd_kalab_file
                $peminjaman->ttd_kalab_file = $user->signature_path;

                $message = 'Peminjaman disetujui Kalab';

                $actionType = 'approve_final';
                $recipient = $peminjaman->mahasiswa ?? null;
                $recipient->notify(new PeminjamanNotification($peminjaman, $actionType, Auth::user()));
            } else {
                return response()->json(['success' => false, 'message' => 'Status tidak valid untuk approval Kalab'], 400);
            }
        } else {
            // Admin atau jabatan lain langsung approve
            $peminjaman->status = 'disetujui';
            $message = 'Peminjaman disetujui';

            $actionType = 'approve_final';
            $recipient = $peminjaman->mahasiswa ?? null;
        }

        DB::transaction(function () use ($peminjaman) {
            $peminjaman->save();

            if ($peminjaman->status === 'disetujui') {
                $period = \Carbon\CarbonPeriod::create(
                    $peminjaman->waktu_mulai->format('Y-m-d'),
                    $peminjaman->waktu_selesai->format('Y-m-d')
                );

                foreach ($period as $date) {
                    \App\Models\PresensiLab::firstOrCreate([
                        'peminjaman_lab_id' => $peminjaman->id,
                        'tanggal_presensi' => $date->format('Y-m-d'),
                    ], [
                        'mahasiswa_id' => $peminjaman->mahasiswa_id,
                        'status_presensi' => 'belum_hadir',
                    ]);
                }
            }
        });

        if ($peminjaman->status === 'disetujui') {
            // Persetujuan final, kirim notifikasi ke Mahasiswa
            $peminjaman->mahasiswa->notify(new PeminjamanNotification($peminjaman, 'approve_final', Auth::user()));
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function reject($id)
    {
        $peminjaman = PeminjamanLab::findOrFail($id);
        $peminjaman->status = 'ditolak';
        $peminjaman->save();

        // Kirim notifikasi penolakan ke Mahasiswa
        $peminjaman->mahasiswa->notify(new PeminjamanNotification($peminjaman, 'reject', Auth::user()));

        return response()->json(['success' => true, 'message' => 'Peminjaman ditolak']);
    }

    public function rejectWithNote(Request $request, $id)
    {
        $request->validate([
            'catatan_tolak' => 'required|string',
        ]);

        $peminjaman = PeminjamanLab::findOrFail($id);
        $peminjaman->status = 'ditolak';
        $peminjaman->catatan_tolak = $request->catatan_tolak;
        $peminjaman->save();

        // Kirim notifikasi penolakan dengan catatan ke Mahasiswa
        $peminjaman->mahasiswa->notify(new PeminjamanNotification($peminjaman, 'reject', Auth::user(), $peminjaman->catatan_tolak));

        return response()->json(['success' => true, 'message' => 'Peminjaman ditolak dengan catatan']);
    }
}
