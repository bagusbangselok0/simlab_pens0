<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanLab;
use App\Models\PresensiLab;
use App\Models\User;
use App\Notifications\PresensiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    public function index()
    {
        $title = 'Presensi Laboratorium';

        // Get peminjaman yang sudah disetujui untuk mahasiswa yang sedang login
        $peminjamanLabs = PeminjamanLab::where('mahasiswa_id', Auth::id())
            ->where('status', 'disetujui')
            ->with(['lab', 'presensiHariIni'])
            ->orderBy('waktu_mulai', 'desc')
            ->get();

        // Get daftar satpam yang aktif
        $satpamList = User::whereHas('role', function ($query) {
            $query->where('slug', 'satpam');
        })
            ->where('is_active', true)
            ->orderBy('nama_asli')
            ->get();

        return view('pages.presensi_lab.index', compact('title', 'peminjamanLabs', 'satpamList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman_lab,id',
            'tipe_presensi' => 'required|in:masuk,keluar',
            'satpam_id' => 'required|exists:users,id',
        ], [
            'peminjaman_id.required' => 'Peminjaman lab harus dipilih',
            'peminjaman_id.exists' => 'Peminjaman lab tidak ditemukan',
            'tipe_presensi.required' => 'Tipe presensi harus dipilih',
            'tipe_presensi.in' => 'Tipe presensi tidak valid',
            'satpam_id.required' => 'Satpam harus dipilih',
            'satpam_id.exists' => 'Satpam tidak ditemukan',
        ]);

        $peminjaman = PeminjamanLab::findOrFail($request->peminjaman_id);

        // Pastikan peminjaman milik user yang sedang login
        if ($peminjaman->mahasiswa_id !== Auth::id()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke peminjaman ini.',
                ], 403);
            }
            return back()->withErrors(['peminjaman_id' => 'Anda tidak memiliki akses ke peminjaman ini.']);
        }

        // Pastikan peminjaman sudah disetujui
        if ($peminjaman->status !== 'disetujui') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Peminjaman lab belum disetujui.',
                ], 400);
            }
            return back()->withErrors(['peminjaman_id' => 'Peminjaman lab belum disetujui.']);
        }

        $now = now('Asia/Jakarta'); // pastikan menggunakan zona waktu Indonesia
        $batasMalam = now('Asia/Jakarta')->setTime(21, 0, 0); // 21:00 malam
        $batasMalamKeluar = now('Asia/Jakarta')->setTime(22, 0, 0); // 22:00 malam
        $todayDate = $now->toDateString();

        // Ambil data presensi hari ini, jika belum ada (fallback) buat baru
        $presensiHariIni = $peminjaman->presensiHariIni;
        if (!$presensiHariIni) {
            $presensiHariIni = PresensiLab::firstOrCreate([
                'peminjaman_lab_id' => $peminjaman->id,
                'tanggal_presensi' => $todayDate,
            ], [
                'mahasiswa_id' => $peminjaman->mahasiswa_id,
                'status_presensi' => 'belum_hadir',
            ]);
        }

        if ($request->tipe_presensi === 'masuk') {
            // Validasi waktu presensi masuk
            if ($now->lt($peminjaman->waktu_mulai)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Presensi masuk belum bisa dilakukan. Waktu peminjaman dimulai pada ' . $peminjaman->waktu_mulai->format('d/m/Y H:i') . '.', // zonasi waktu Indonesia
                    ], 400);
                }
                return back()->withErrors(['tipe_presensi' => 'Presensi masuk belum bisa dilakukan. Waktu peminjaman dimulai pada ' . $peminjaman->waktu_mulai->format('d/m/Y H:i') . '.']);
            }

            if ($now->gt($batasMalam)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Presensi masuk tidak bisa dilakukan setelah jam 21:00.',
                    ], 400);
                }
                return back()->withErrors(['tipe_presensi' => 'Presensi masuk tidak bisa dilakukan setelah jam 21:00.']);
            }

            // Cek apakah sudah ada presensi masuk yang aktif hari ini
            if ($presensiHariIni->status_presensi === 'didalam') {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan presensi masuk dan sedang berada di dalam lab.',
                    ], 400);
                }
                return back()->withErrors(['tipe_presensi' => 'Anda sudah melakukan presensi masuk hari ini dan sedang berada di dalam lab.']);
            }

            // Cek jika presensi hari ini sudah selesai
            if (in_array($presensiHariIni->status_presensi, ['selesai', 'menunggu_konfirmasi_keluar'])) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah menyelesaikan presensi untuk hari ini.',
                    ], 400);
                }
                return back()->withErrors(['tipe_presensi' => 'Anda sudah menyelesaikan presensi untuk hari ini.']);
            }

            // Cek jika presensi hari ini masih menunggu konfirmasi masuk
            if ($presensiHariIni->status_presensi === 'menunggu_konfirmasi_masuk') {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sedang menunggu konfirmasi presensi masuk dari satpam untuk hari ini.',
                    ], 400);
                }
                return back()->withErrors(['tipe_presensi' => 'Anda sedang menunggu konfirmasi presensi masuk dari satpam untuk hari ini.']);
            }

            // Update presensi masuk
            $presensiHariIni->update([
                'satpam_masuk_id' => $request->satpam_id,
                'status_presensi' => 'menunggu_konfirmasi_masuk',
            ]);

            $satpam = User::find($request->satpam_id);
            if ($satpam) {
                $satpam->notify(new PresensiNotification($peminjaman, 'masuk', $peminjaman->mahasiswa, $presensiHariIni));
            }

            $message = 'Presensi masuk berhasil diajukan. Menunggu konfirmasi dari satpam.';
        } elseif ($request->tipe_presensi === 'keluar') {
            // Validasi waktu presensi keluar
            if ($now->gt($batasMalamKeluar)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Presensi keluar tidak bisa dilakukan setelah jam 22:00.',
                    ], 400);
                }
                return back()->withErrors(['tipe_presensi' => 'Presensi keluar tidak bisa dilakukan setelah jam 22:00.']);
            }

            // Cek apakah sudah ada presensi keluar yang aktif hari ini
            if ($presensiHariIni->status_presensi === 'menunggu_konfirmasi_keluar') {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan presensi keluar dan sedang menunggu konfirmasi dari satpam.',
                    ], 400);
                }
                return back()->withErrors(['tipe_presensi' => 'Anda sudah melakukan presensi keluar dan sedang menunggu konfirmasi dari satpam.']);
            }

            // Pastikan sudah ada presensi masuk yang aktif hari ini
            if ($presensiHariIni->status_presensi !== 'didalam') {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda belum melakukan presensi masuk atau sudah keluar dari lab.',
                    ], 400);
                }
                return back()->withErrors(['tipe_presensi' => 'Anda belum melakukan presensi masuk hari ini atau sudah keluar dari lab.']);
            }

            // Update presensi hari ini untuk presensi keluar
            $presensiHariIni->update([
                'satpam_keluar_id' => $request->satpam_id,
                'status_presensi' => 'menunggu_konfirmasi_keluar',
            ]);

            $satpam = User::find($request->satpam_id);
            if ($satpam) {
                $satpam->notify(new PresensiNotification($peminjaman, 'keluar', $peminjaman->mahasiswa, $presensiHariIni));
            }

            $message = 'Presensi keluar berhasil diajukan. Menunggu konfirmasi dari satpam.';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function cetak($id)
    {
        $peminjaman = PeminjamanLab::with(['lab', 'labManager.plp', 'labManager.kalab', 'mahasiswa'])
            ->where('id', $id)
            ->firstOrFail();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.peminjaman.cetak', compact('peminjaman'));

        $nama_file = 'Peminjaman_Lab_' . str_replace(' ', '_', $peminjaman->lab->nama_lab) . '_' . date('YmdHis') . '.pdf';
        return $pdf->stream($nama_file);
    }

    public function indexSatpam()
    {
        $title = 'Konfirmasi Presensi Lab';

        // Get semua presensi yang menunggu konfirmasi
        $presensiMenunggu = PresensiLab::with(['peminjamanLab.lab', 'mahasiswa', 'satpamMasuk', 'satpamKeluar'])
            ->whereIn('status_presensi', ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.presensi_satpam.index', compact('title', 'presensiMenunggu'));
    }

    public function confirmPresence(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        $presensi = PresensiLab::findOrFail($id);

        // Pastikan presensi dalam status menunggu konfirmasi
        if (!in_array($presensi->status_presensi, ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Presensi ini sudah dikonfirmasi atau tidak valid.',
                ], 400);
            }
            return back()->withErrors(['action' => 'Presensi ini sudah dikonfirmasi atau tidak valid.']);
        }

        if ($request->action === 'approve') {
            if ($presensi->status_presensi === 'menunggu_konfirmasi_masuk') {
                // Konfirmasi presensi masuk
                $presensi->update([
                    'jam_masuk' => now('Asia/Jakarta'),
                    'status_presensi' => 'didalam',
                ]);
                $message = 'Presensi masuk berhasil dikonfirmasi.';
            } elseif ($presensi->status_presensi === 'menunggu_konfirmasi_keluar') {
                // Konfirmasi presensi keluar
                $presensi->update([
                    'jam_keluar' => now('Asia/Jakarta'),
                    'status_presensi' => 'selesai',
                ]);
                $message = 'Presensi keluar berhasil dikonfirmasi.';
            }
        } elseif ($request->action === 'reject') {
            // Tolak presensi - kembalikan ke status sebelumnya
            if ($presensi->status_presensi === 'menunggu_konfirmasi_masuk') {
                // Hapus presensi masuk yang ditolak
                $presensi->delete();
                $message = 'Presensi masuk ditolak.';
            } elseif ($presensi->status_presensi === 'menunggu_konfirmasi_keluar') {
                // Kembalikan ke status didalam
                $presensi->update([
                    'satpam_keluar_id' => null,
                    'status_presensi' => 'didalam',
                ]);
                $message = 'Presensi keluar ditolak.';
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function assignSatpam(Request $request, $id)
    {
        $presensi = PresensiLab::findOrFail($id);

        if (!in_array($presensi->status_presensi, ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Presensi ini tidak berada dalam status menunggu konfirmasi.',
                ], 400);
            }
            return back()->withErrors(['presensi' => 'Presensi ini tidak berada dalam status menunggu konfirmasi.']);
        }

        if ($presensi->status_presensi === 'menunggu_konfirmasi_masuk') {
            $presensi->update(['satpam_masuk_id' => Auth::id()]);
            $message = 'Satpam bertugas berhasil diperbarui untuk presensi masuk.';
        } else {
            $presensi->update(['satpam_keluar_id' => Auth::id()]);
            $message = 'Satpam bertugas berhasil diperbarui untuk presensi keluar.';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function riwayat(Request $request)
    {
        $title = 'Riwayat Presensi Lab';

        $query = PresensiLab::with(['peminjamanLab.lab', 'satpamMasuk', 'satpamKeluar'])
            ->where('mahasiswa_id', Auth::id())
            ->orderBy('tanggal_presensi', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status_presensi', $request->status);
        }

        // Filter berdasarkan tanggal mulai
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_presensi', '>=', $request->tanggal_mulai);
        }

        // Filter berdasarkan tanggal selesai
        if ($request->filled('tanggal_selesai')) {
            $query->where('tanggal_presensi', '<=', $request->tanggal_selesai);
        }

        // Filter berdasarkan lab
        if ($request->filled('lab')) {
            $query->whereHas('peminjamanLab.lab', function ($q) use ($request) {
                $q->where('id', $request->lab);
            });
        }

        $riwayatPresensi = $query->paginate(15)->withQueryString();

        // Ambil daftar lab yang pernah dipinjam oleh mahasiswa ini untuk dropdown filter
        $labList = \App\Models\Lab::whereHas('peminjamanLabs', function ($q) {
            $q->where('mahasiswa_id', Auth::id());
        })->orderBy('nama_lab')->get();

        // Statistik presensi
        $totalPresensi = PresensiLab::where('mahasiswa_id', Auth::id())->count();
        $totalSelesai = PresensiLab::where('mahasiswa_id', Auth::id())->where('status_presensi', 'selesai')->count();
        $totalDidalam = PresensiLab::where('mahasiswa_id', Auth::id())->where('status_presensi', 'didalam')->count();
        $totalTidakHadir = PresensiLab::where('mahasiswa_id', Auth::id())->where('status_presensi', 'tidak_hadir')->count();

        return view('pages.presensi_lab.riwayat', compact(
            'title',
            'riwayatPresensi',
            'labList',
            'totalPresensi',
            'totalSelesai',
            'totalDidalam',
            'totalTidakHadir'
        ));
    }

    public function indexMonitoring(Request $request)
    {
        $title = 'Monitoring Presensi Lab';

        if ($request->ajax()) {
            $status = $request->query('status');

            $query = PresensiLab::with(['peminjamanLab.lab', 'mahasiswa', 'satpamMasuk', 'satpamKeluar'])
                ->orderBy('created_at', 'desc');

            if ($status === 'didalam') {
                $query->where('status_presensi', 'didalam');
            } elseif ($status === 'selesai') {
                $query->where('status_presensi', 'selesai');
            } else {
                $query->whereIn('status_presensi', ['didalam', 'menunggu_konfirmasi_keluar', 'selesai']);
            }

            $presensiAll = $query->get();

            $data = $presensiAll->map(function (PresensiLab $presensi) {
                return [
                    'id' => $presensi->id,
                    'mahasiswa_name' => $presensi->mahasiswa->full_name ?? '-',
                    'mahasiswa_email' => $presensi->mahasiswa->email ?? '-',
                    'lab_name' => $presensi->peminjamanLab->lab->nama_lab ?? '-',
                    'tujuan' => $presensi->peminjamanLab->tujuan ?? '-',
                    'status_presensi' => $presensi->status_presensi,
                    'satpam_masuk' => $presensi->satpamMasuk->full_name ?? 'N/A',
                    'satpam_keluar' => $presensi->satpamKeluar->full_name ?? 'N/A',
                    'created_at' => optional($presensi->created_at)->format('d/m/Y H:i'),
                    'jam_masuk' => optional($presensi->jam_masuk)->format('d/m/Y H:i'),
                    'jam_keluar' => optional($presensi->jam_keluar)->format('d/m/Y H:i'),
                    'peminjaman_lab_id' => $presensi->peminjamanLab->id,
                ];
            });

            return response()->json(['data' => $data]);
        }

        return view('pages.presensi_monitoring.index', compact('title'));
    }
}
