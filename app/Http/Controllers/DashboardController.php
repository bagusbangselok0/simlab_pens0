<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\LabManager;
use App\Models\PeminjamanLab;
use App\Models\PresensiLab;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil slug role, pastikan huruf kecil agar aman di Linux/Web Server
        $roleSlug = strtolower($user->role->slug);

        $labs = Lab::all();
        $lab_managers = LabManager::with(['lab', 'plp', 'kalab'])->get();

        // Statistik jumlah pengguna per role
        $jml_mahasiswa = User::whereHas('role', fn($q) => $q->where('slug', 'mahasiswa'))->count();
        $jml_dosen     = User::whereHas('role', fn($q) => $q->where('slug', 'dosen'))->count();
        $jml_satpam    = User::whereHas('role', fn($q) => $q->where('slug', 'satpam'))->count();
        $jml_plp       = User::whereHas('role', fn($q) => $q->where('slug', 'plp'))->count();

        // Statistik peminjaman mahasiswa yang login (untuk dashboard mahasiswa)
        $jml_pengajuan_mhs = PeminjamanLab::where('mahasiswa_id', Auth::id())->count();
        $jml_pending_mhs   = PeminjamanLab::where('mahasiswa_id', Auth::id())->whereIn('status', ['pending_plp', 'pending_kalab'])->count();
        $jml_approved_mhs  = PeminjamanLab::where('mahasiswa_id', Auth::id())->where('status', 'disetujui')->count();
        $jml_rejected_mhs  = PeminjamanLab::where('mahasiswa_id', Auth::id())->where('status', 'ditolak')->count();

        $pinjaman_lab_terakhir = PeminjamanLab::where('mahasiswa_id', Auth::id())->latest()->first();

        // ====================================================
        // Statistik khusus Admin (semua peminjaman di sistem)
        // ====================================================
        $total_peminjaman  = PeminjamanLab::count();
        $pending_admin     = PeminjamanLab::whereIn('status', ['pending_plp', 'pending_kalab'])->count();
        $approved_admin    = PeminjamanLab::where('status', 'disetujui')->count();
        $rejected_admin    = PeminjamanLab::where('status', 'ditolak')->count();
        $kadaluarsa_admin  = PeminjamanLab::where('status', 'kadaluarsa')->count();

        // ====================================================
        // Statistik khusus Kalab (fokus pada lab tanggung jawab Kalab)
        // ====================================================
        $my_lab_managers = LabManager::with(['lab', 'plp'])
            ->where('kalab_id', Auth::id())
            ->get();
        $my_lab_ids = $my_lab_managers->pluck('lab_id');

        $total_lab_kalab = $my_lab_managers->count();
        $total_mahasiswa_kalab = PeminjamanLab::whereIn('lab_id', $my_lab_ids)
            ->distinct('mahasiswa_id')
            ->count('mahasiswa_id');

        $total_peminjaman_by_kalab = PeminjamanLab::whereIn('lab_id', $my_lab_ids)->count();
        $disetujui_kalab = PeminjamanLab::whereIn('lab_id', $my_lab_ids)->where('status', 'disetujui')->count();
        $selesai_kalab = PeminjamanLab::whereIn('lab_id', $my_lab_ids)->where('status', 'selesai')->count();
        $tolak_kalab = PeminjamanLab::whereIn('lab_id', $my_lab_ids)->where('status', 'ditolak')->count();
        $pending_kalab = PeminjamanLab::whereIn('lab_id', $my_lab_ids)->where('status', 'pending_kalab')->count();
        $kadaluarsa_batal_kalab = PeminjamanLab::whereIn('lab_id', $my_lab_ids)
            ->whereIn('status', ['kadaluarsa', 'dibatalkan', 'dibatalkan_mahasiswa'])
            ->count();

        $pengajuan_terbaru_kalab = PeminjamanLab::with(['mahasiswa', 'lab'])
            ->whereIn('lab_id', $my_lab_ids)
            ->latest()
            ->take(5)
            ->get();

        // Peminjaman aktif hari ini tapi belum presensi masuk (khusus lab Kalab)
        $today = now('Asia/Jakarta')->toDateString();
        $belum_presensi_hari_ini_kalab = PeminjamanLab::whereIn('lab_id', $my_lab_ids)
            ->where('status', 'disetujui')
            ->whereDate('waktu_mulai', '<=', $today)
            ->whereDate('waktu_selesai', '>=', $today)
            ->where(function ($q) use ($today) {
                $q->whereDoesntHave('presensi', function ($pq) use ($today) {
                    $pq->whereDate('tanggal_presensi', $today);
                })->orWhereHas('presensi', function ($pq) use ($today) {
                    $pq->whereDate('tanggal_presensi', $today)
                        ->whereIn('status_presensi', ['belum_hadir', 'menunggu_konfirmasi_masuk']);
                });
            })
            ->count();

        // Mahasiswa yang sedang berada di lab Kalab saat ini (presensi status: didalam)
        $sedang_di_lab_kalab = PresensiLab::whereHas('peminjamanLab', function ($q) use ($my_lab_ids) {
            $q->whereIn('lab_id', $my_lab_ids);
        })->where('status_presensi', 'didalam')->count();

        // Data Grafik 1: Tren Peminjaman Lab Kalab (6 Bulan Terakhir)
        $tren_kalab_labels = [];
        $tren_kalab_data = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now('Asia/Jakarta')->subMonths($i);
            $tren_kalab_labels[] = $monthDate->locale('id')->translatedFormat('M Y');
            $tren_kalab_data[] = PeminjamanLab::whereIn('lab_id', $my_lab_ids)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->count();
        }

        // Data Grafik 2: Komposisi Status Peminjaman di Lab Kalab
        $status_kalab_chart = [
            'labels' => ['Disetujui & Selesai', 'Menunggu Persetujuan', 'Ditolak', 'Batal / Kadaluarsa'],
            'series' => [
                $disetujui_kalab + $selesai_kalab,
                $pending_kalab,
                $tolak_kalab,
                $kadaluarsa_batal_kalab,
            ]
        ];

        // ====================================================
        // Statistik khusus PLP (semua peminjaman di sistem)
        // ====================================================
        $total_peminjaman_by_plp = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('plp_id', Auth::id());
        })->count();
        $selesai_plp = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('plp_id', Auth::id());
        })->where('status', 'selesai')->count();
        $tolak_plp = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('plp_id', Auth::id());
        })->where('status', 'ditolak')->count();
        $pending_plp = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('plp_id', Auth::id());
        })->where('status', 'pending_plp')->count();
        $pengajuan_terbaru_plp = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('plp_id', Auth::id());
        })->latest()->take(5)->get();

        // Peminjaman per lab (untuk chart bar admin/plp)
        $peminjaman_per_lab = Lab::withCount('peminjamanLabs')->get();

        // 5 pengajuan terbaru untuk tabel ringkasan admin
        $pengajuan_terbaru = PeminjamanLab::with(['mahasiswa', 'lab'])
            ->latest()
            ->take(5)
            ->get();

        // Peminjaman aktif hari ini (status disetujui & waktu mencakup hari ini - admin)
        $peminjaman_hari_ini = PeminjamanLab::where('status', 'disetujui')
            ->whereDate('waktu_mulai', '<=', now())
            ->whereDate('waktu_selesai', '>=', now())
            ->count();

        // ====================================================
        // Statistik khusus Satpam
        // ====================================================

        $todayDateSatpam = now('Asia/Jakarta')->toDateString();

        // Total presensi yang sudah aktif/hadir hari ini (status bukan belum_hadir)
        $presensi_hari_ini = PresensiLab::whereDate('tanggal_presensi', $todayDateSatpam)
            ->where('status_presensi', '!=', 'belum_hadir')
            ->count();

        // Total mahasiswa/peminjaman yang terjadwal presensi hari ini
        $total_jadwal_hari_ini = PresensiLab::whereDate('tanggal_presensi', $todayDateSatpam)->count();

        // Presensi menunggu konfirmasi (semua satpam)
        $presensi_menunggu = PresensiLab::whereIn('status_presensi', [
            'menunggu_konfirmasi_masuk',
            'menunggu_konfirmasi_keluar'
        ])->count();

        // Daftar presensi menunggu (maks 5, untuk tabel dashboard)
        $presensi_list_menunggu = PresensiLab::with(['peminjamanLab.lab', 'mahasiswa', 'satpamMasuk', 'satpamKeluar'])
            ->whereIn('status_presensi', ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Mahasiswa yang sedang di dalam lab (status 'didalam')
        $mahasiswa_didalam = PresensiLab::where('status_presensi', 'didalam')->count();

        // Lab yang saat ini sedang aktif digunakan oleh mahasiswa
        $lab_digunakan_count = Lab::whereHas('peminjamanLabs.presensi', function ($q) {
            $q->where('status_presensi', 'didalam');
        })->distinct()->count();

        $mahasiswa_didalam_list = PresensiLab::with(['peminjamanLab.lab', 'mahasiswa'])
            ->where('status_presensi', 'didalam')
            ->orderBy('jam_masuk', 'desc')
            ->take(5)
            ->get();

        // Statistik konfirmasi oleh satpam yang login
        $total_konfirmasi_saya = PresensiLab::where(function ($q) {
            $q->where('satpam_masuk_id', Auth::id())
                ->orWhere('satpam_keluar_id', Auth::id());
        })->whereNotIn('status_presensi', ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])
            ->count();

        $konfirmasi_masuk_saya = PresensiLab::where('satpam_masuk_id', Auth::id())
            ->whereNotIn('status_presensi', ['menunggu_konfirmasi_masuk'])
            ->count();

        $konfirmasi_keluar_saya = PresensiLab::where('satpam_keluar_id', Auth::id())
            ->where('status_presensi', 'selesai')
            ->count();

        $konfirmasi_saya_hari_ini = PresensiLab::where(function ($q) {
            $q->where('satpam_masuk_id', Auth::id())
                ->orWhere('satpam_keluar_id', Auth::id());
        })->whereDate('updated_at', today())
            ->whereNotIn('status_presensi', ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])
            ->count();

        // Riwayat konfirmasi presensi terbaru (5 terakhir yang sudah dikonfirmasi)
        $riwayat_konfirmasi = PresensiLab::with(['peminjamanLab.lab', 'mahasiswa'])
            ->whereIn('status_presensi', ['didalam', 'selesai'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Presensi per lab (untuk chart bar satpam)
        $presensi_per_lab = Lab::selectRaw('labs.id, labs.nama_lab, labs.kode_lab, COUNT(presensi_lab.id) as presensi_count')
            ->leftJoin('peminjaman_lab', 'labs.id', '=', 'peminjaman_lab.lab_id')
            ->leftJoin('presensi_lab', 'peminjaman_lab.id', '=', 'presensi_lab.peminjaman_lab_id')
            ->groupBy('labs.id', 'labs.nama_lab', 'labs.kode_lab')
            ->get();

        // Data Grafik Analisa Satpam:
        // 1. Tren Lalu Lintas Presensi 7 Hari Terakhir (Masuk vs Keluar)
        $tren_satpam_labels = [];
        $tren_satpam_masuk = [];
        $tren_satpam_keluar = [];
        for ($i = 6; $i >= 0; $i--) {
            $tDate = now('Asia/Jakarta')->subDays($i);
            $tDateStr = $tDate->toDateString();
            $tren_satpam_labels[] = $tDate->locale('id')->translatedFormat('d M');
            $tren_satpam_masuk[] = PresensiLab::whereDate('tanggal_presensi', $tDateStr)
                ->whereNotNull('jam_masuk')
                ->count();
            $tren_satpam_keluar[] = PresensiLab::whereDate('tanggal_presensi', $tDateStr)
                ->whereNotNull('jam_keluar')
                ->count();
        }

        // 2. Status Kehadiran Hari Ini (Donut Chart)
        $status_satpam_chart = [
            'didalam'     => PresensiLab::whereDate('tanggal_presensi', $todayDateSatpam)->where('status_presensi', 'didalam')->count(),
            'selesai'     => PresensiLab::whereDate('tanggal_presensi', $todayDateSatpam)->where('status_presensi', 'selesai')->count(),
            'menunggu'    => PresensiLab::whereDate('tanggal_presensi', $todayDateSatpam)->whereIn('status_presensi', ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])->count(),
            'belum_hadir' => PresensiLab::whereDate('tanggal_presensi', $todayDateSatpam)->where('status_presensi', 'belum_hadir')->count(),
        ];

        // title dinamis berdasarkan role
        $title = match ($roleSlug) {
            'admin'     => 'Dashboard Admin',
            'plp'       => 'Dashboard PLP',
            'dosen'     => 'Dashboard Dosen',
            'satpam'    => 'Dashboard Satpam',
            'mahasiswa' => 'Dashboard Mahasiswa',
            default     => 'Dashboard',
        };

        $show_signature_alert = in_array($roleSlug, ['plp', 'dosen']) && empty($user->signature_path);

        $data = compact(
            'user',
            'title',
            'jml_mahasiswa',
            'jml_dosen',
            'jml_satpam',
            'jml_plp',
            'jml_pengajuan_mhs',
            'jml_pending_mhs',
            'jml_approved_mhs',
            'jml_rejected_mhs',
            'pinjaman_lab_terakhir',
            'labs',
            'lab_managers',
            'total_peminjaman',
            'pending_admin',
            'approved_admin',
            'rejected_admin',
            'kadaluarsa_admin',
            'peminjaman_per_lab',
            'pengajuan_terbaru',
            'peminjaman_hari_ini',
            'my_lab_managers',
            'total_lab_kalab',
            'total_mahasiswa_kalab',
            'belum_presensi_hari_ini_kalab',
            'sedang_di_lab_kalab',
            'tren_kalab_labels',
            'tren_kalab_data',
            'status_kalab_chart',
            'total_peminjaman_by_kalab',
            'selesai_kalab',
            'tolak_kalab',
            'pending_kalab',
            'pengajuan_terbaru_kalab',
            'total_peminjaman_by_plp',
            'selesai_plp',
            'tolak_plp',
            'pending_plp',
            'pengajuan_terbaru_plp',
            'presensi_hari_ini',
            'total_jadwal_hari_ini',
            'presensi_menunggu',
            'presensi_list_menunggu',
            'mahasiswa_didalam',
            'mahasiswa_didalam_list',
            'lab_digunakan_count',
            'total_konfirmasi_saya',
            'konfirmasi_masuk_saya',
            'konfirmasi_keluar_saya',
            'konfirmasi_saya_hari_ini',
            'riwayat_konfirmasi',
            'presensi_per_lab',
            'tren_satpam_labels',
            'tren_satpam_masuk',
            'tren_satpam_keluar',
            'status_satpam_chart',
            'show_signature_alert'
        );

        // Cek apakah file view spesifik role ada
        if (view()->exists("pages.dashboards.{$roleSlug}")) {
            return view("pages.dashboards.{$roleSlug}", $data);
        }

        // Jika file spesifik tidak ada, lari ke default
        return view('pages.dashboards.default', $data);
    }
}
