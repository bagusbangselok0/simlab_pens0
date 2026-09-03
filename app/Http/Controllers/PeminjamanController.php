<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanLab;
use App\Models\Lab;
use App\Models\User;
use App\Notifications\PeminjamanNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class PeminjamanController extends Controller
{
    // Menampilkan daftar semua pinjaman (Untuk Admin)
    public function indexAdmin(Request $request)
    {
        $title = 'Semua Peminjaman Lab';
        $students = User::where('role_id', 5)->orderBy('nama_asli')->get();

        if ($request->ajax()) {
            $query = PeminjamanLab::with(['mahasiswa', 'lab'])
                ->orderBy('created_at', 'desc');

            if ($request->mahasiswa_id) {
                $query->where('mahasiswa_id', $request->mahasiswa_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('mahasiswa', function ($row) {
                    return $row->mahasiswa->nama_asli . '<br><small class="text-muted">' . ($row->mahasiswa->nrp ?? '-') . '</small>';
                })
                ->addColumn('nama_lab', function ($row) {
                    return $row->lab->nama_lab . ' (' . $row->lab->kode_lab . ')';
                })
                ->addColumn('keperluan', function ($row) {
                    return $row->tujuan;
                })
                ->addColumn('waktu_mulai', function ($row) {
                    return $row->waktu_mulai->format('d-m-Y H:i');
                })
                ->addColumn('waktu_selesai', function ($row) {
                    return $row->waktu_selesai->format('d-m-Y H:i');
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
                    } elseif ($row->status == 'dibatalkan') {
                        return '<span class="badge bg-danger">Dibatalkan Sistem</span>';
                    } elseif ($row->status == 'dibatalkan_mahasiswa') {
                        return '<span class="badge bg-danger">Dibatalkan Mahasiswa</span>';
                    }
                })
                ->addColumn('action', function ($pinjam) {
                    return '<a href="javascript:void(0)" class="btn btn-sm btn-info detailData" data-id="' . $pinjam->id . '">Detail</a>';
                })
                ->rawColumns(['mahasiswa', 'status', 'action'])
                ->make(true);
        }

        return view('pages.peminjaman.admin_index', compact('title', 'students'));
    }

    // Menampilkan daftar pinjaman milik mahasiswa yang sedang login
    public function index()
    {
        $title = 'Daftar Peminjaman Lab';
        $labs = Lab::all(); // Untuk dropdown filter jika diperlukan


        if (request()->ajax()) {
            // Mengambil daftar pinjaman milik mahasiswa
            $pinjamans = PeminjamanLab::where('mahasiswa_id', Auth::id())
                ->with('lab') // Eager loading untuk performa
                ->orderBy('created_at', 'desc')
                ->get();

            return DataTables::of($pinjamans)
                ->addIndexColumn()
                ->addColumn('nama_lab', function ($row) {
                    return $row->lab->nama_lab . ' (' . $row->lab->kode_lab . ')';
                })
                ->addColumn('keperluan', function ($row) {
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
                    } elseif ($row->status == 'dibatalkan') {
                        return '<span class="badge bg-danger">Dibatalkan Sistem</span>';
                    } elseif ($row->status == 'dibatalkan_mahasiswa') {
                        return '<span class="badge bg-danger">Dibatalkan Anda</span>';
                    }
                })
                ->addColumn('action', function ($pinjam) {
                    $btn = '<a href="#" class="btn btn-sm btn-info detailData" data-id="' . $pinjam->id . '" data-bs-toggle="modal" data-bs-target="#detailModal">Detail</a>';
                    if (in_array($pinjam->status, ['pending_plp', 'pending_kalab'])) {
                        $btn .= ' <button class="btn btn-sm btn-danger cancelData" data-id="' . $pinjam->id . '">Batalkan</button>';
                    }
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('pages.peminjaman.index', compact('title', 'labs'));
    }

    public function detailModal($id)
    {
        $penolak = null;
        $peminjaman = PeminjamanLab::with(['lab', 'labManager.plp', 'labManager.kalab', 'mahasiswa'])
            ->where('id', $id);

        // Jika bukan admin, hanya bisa melihat milik sendiri
        if (Auth::user()->role_id != 1) {
            $peminjaman->where('mahasiswa_id', Auth::id());
        }

        $peminjaman = $peminjaman->firstOrFail();

        // Jika tgl_ttd_plp kosong berarti di tolak oleh PLP, jika tgl_ttd_kalab kosong berarti di tolak oleh Kalab
        if ($peminjaman->status == 'ditolak') {
            if (!$peminjaman->tgl_ttd_plp) {
                $penolak = 'PLP';
            } elseif (!$peminjaman->tgl_ttd_kalab) {
                $penolak = 'Kalab';
            }
        }

        $plpName = optional($peminjaman->labManager->plp)->full_name ?: optional($peminjaman->labManager->plp)->nama_asli ?: '-';
        $kalabName = optional($peminjaman->labManager->kalab)->full_name ?: optional($peminjaman->labManager->kalab)->nama_asli ?: '-';
        $statusBadge = '';

        if ($peminjaman->status == 'pending_plp') {
            $statusBadge = '<span class="badge bg-warning">Menunggu PLP</span>';
        } elseif ($peminjaman->status == 'pending_kalab') {
            $statusBadge = '<span class="badge bg-info">Menunggu Kalab</span>';
        } elseif ($peminjaman->status == 'disetujui') {
            $statusBadge = '<span class="badge bg-success">Disetujui</span>';
        } elseif ($peminjaman->status == 'ditolak') {
            $statusBadge = '<span class="badge bg-danger">Ditolak</span>';
        } elseif ($peminjaman->status == 'kadaluarsa') {
            $statusBadge = '<span class="badge bg-secondary">Kadaluarsa</span>';
        } elseif ($peminjaman->status == 'dibatalkan') {
            $statusBadge = '<span class="badge bg-danger">Dibatalkan Sistem</span>';
        } elseif ($peminjaman->status == 'dibatalkan_mahasiswa') {
            $statusBadge = '<span class="badge bg-danger">Dibatalkan Anda</span>';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nama_lab' => $peminjaman->lab->nama_lab,
                'plp' => $plpName,
                'tg_ttd_plp' => $peminjaman->tgl_ttd_plp ? $peminjaman->tgl_ttd_plp->format('d-m-Y H:i') : '-',
                'kalab' => $kalabName,
                'tg_ttd_kalab' => $peminjaman->tgl_ttd_kalab ? $peminjaman->tgl_ttd_kalab->format('d-m-Y H:i') : '-',
                'catatan_tolak' => $peminjaman->catatan_tolak ?? '-',
                'penolak' => $penolak ? '(Ditolak oleh ' . $penolak . ')' : '-',
                'status_badge' => $statusBadge,
                'ttd_mahasiswa_file' => $peminjaman->ttd_mahasiswa_file,
                'ttd_plp_file' => $peminjaman->ttd_plp_file,
                'ttd_kalab_file' => $peminjaman->ttd_kalab_file,
                'created_at' => $peminjaman->created_at->format('d-m-Y H:i'),
            ],
        ], 200);
    }

    public function cetak($id)
    {
        $peminjaman = PeminjamanLab::with(['lab', 'labManager.plp', 'labManager.kalab', 'mahasiswa'])
            ->where('id', $id)
            ->where('mahasiswa_id', Auth::id())
            ->firstOrFail();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.peminjaman.cetak', compact('peminjaman'));

        $nama_file = 'Peminjaman_Lab_' . str_replace(' ', '_', $peminjaman->lab->nama_lab) . '_' . date('YmdHis') . '.pdf';
        return $pdf->stream($nama_file);
    }

    public function cancel($id)
    {
        $peminjaman = PeminjamanLab::where('id', $id)
            ->where('mahasiswa_id', Auth::id())
            ->firstOrFail();

        if (!in_array($peminjaman->status, ['pending_plp', 'pending_kalab'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan yang sedang menunggu persetujuan yang dapat dibatalkan.'
            ], 422);
        }

        $peminjaman->status = 'dibatalkan_mahasiswa';
        $peminjaman->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dibatalkan.'
        ]);
    }

    // Menyimpan data pengajuan ke database
    public function store(Request $request)
    {
        $messages = [
            'lab_id.required' => 'Lab wajib dipilih.',
            'tujuan.required' => 'Tujuan penggunaan lab wajib diisi.',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi.',
            'waktu_mulai.after' => 'Waktu mulai harus setelah waktu sekarang.',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi.',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ];

        // 1. Validasi (Pesan error dikirim ke sini)
        $request->validate([
            'lab_id' => 'required|exists:labs,id',
            'tujuan' => 'required|string|max:255',
            'waktu_mulai' => 'required|date|after:now',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
        ], $messages);

        // Batas pengajuan 3 kali per hari
        $todayCount = PeminjamanLab::where('mahasiswa_id', Auth::id())
            ->whereDate('created_at', \Carbon\Carbon::today('Asia/Jakarta'))
            ->count();

        if ($todayCount >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Anda telah mencapai batas maksimal pengajuan (3 kali) untuk hari ini. Silakan coba lagi besok.',
            ], 422);
        }


        $lab_manager_id = Lab::find($request->lab_id)->labManager->id ?? null;

        // jika lab belum memiliki penanggung jawab, maka tidak bisa mengajukan peminjaman
        if (!$lab_manager_id) {
            return response()->json([
                'success' => false,
                'message' => 'Laboratorium belum memiliki penanggung jawab. Silakan hubungi admin untuk informasi lebih lanjut.',
            ], 422);
        }

        // Maksimal 7 hari peminjaman
        $waktu_selesai = \Carbon\Carbon::parse($request->waktu_selesai);
        $waktu_mulai = \Carbon\Carbon::parse($request->waktu_mulai);
        $diff = $waktu_mulai->diffInDays($waktu_selesai);
        if ($diff > 7) {
            return response()->json([
                'success' => false,
                'message' => 'Maksimal peminjaman adalah 7 hari.',
            ], 422);
        }

        // Cek konflik waktu peminjaman aktif atau menunggu persetujuan
        $waktu_mulai = Carbon::parse($request->waktu_mulai);
        $waktu_selesai = Carbon::parse($request->waktu_selesai);

        $conflictingPeminjaman = PeminjamanLab::where('mahasiswa_id', Auth::id())
            ->whereIn('status', ['pending_plp', 'pending_kalab', 'disetujui'])
            ->where('waktu_mulai', '<', $waktu_selesai)
            ->where('waktu_selesai', '>', $waktu_mulai)
            ->exists();

        if ($conflictingPeminjaman) {
            $blockedUntil = $waktu_selesai->copy()->addDay();
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat mengajukan peminjaman baru karena sudah ada peminjaman aktif atau menunggu persetujuan yang waktunya bersinggungan dengan rentang yang diajukan. Silakan ajukan setelah ' . $blockedUntil->format('d M Y H:i') . '.',
            ], 422);
        }

        // Cek apakah mahasiswa sudah memiliki tanda tangan yang disetujui Admin
        $userAuth = Auth::user();
        if ($userAuth->signature_path == null || $userAuth->signature_status !== 'approved') {
            if ($userAuth->signature_status === 'pending') {
                $msg = 'Tanda tangan digital Anda masih menunggu persetujuan Admin. Silakan tunggu verifikasi sebelum mengajukan peminjaman.';
            } elseif ($userAuth->signature_status === 'rejected') {
                $msg = 'Tanda tangan digital Anda ditolak oleh Admin. Silakan upload ulang tanda tangan sesuai petunjuk di halaman profil.';
            } else {
                $msg = 'Anda belum memiliki tanda tangan digital yang disetujui Admin. Silakan upload tanda tangan terlebih dahulu di menu profil.';
            }

            return response()->json([
                'success' => false,
                'message' => $msg,
            ], 422);
        }

        $user = User::find(Auth::id());

        // 2. Simpan ke Database
        $peminjaman = PeminjamanLab::create([
            'mahasiswa_id'  => Auth::id(),
            'lab_id'        => $request->lab_id,
            'lab_manager_id' => $lab_manager_id,
            'tujuan'        => $request->tujuan,
            'waktu_mulai'   => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'ttd_mahasiswa_file' => $user->signature_path,
            'status'        => 'pending_plp',
            'created_at'    => now(date_default_timezone_set('Asia/Jakarta')),
            'updated_at'    => now(date_default_timezone_set('Asia/Jakarta')),
        ]);

        if ($peminjaman) {
            // Kirim notifikasi ke PLP penanggung jawab lab
            $labManager = $peminjaman->labManager;
            if ($labManager && $labManager->plp) {
                $labManager->plp->notify(new PeminjamanNotification($peminjaman, 'create', Auth::user()));
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil dikirim!',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan peminjaman. Silakan coba lagi.',
            ], 500);
        }
    }
}
