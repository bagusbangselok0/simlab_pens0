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
        // Statistik khusus Kalab (semua peminjaman di sistem)
        // ====================================================

        $total_peminjaman_by_kalab = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('kalab_id', Auth::id());
        })->count();
        $selesai_kalab = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('kalab_id', Auth::id());
        })->where('status', 'selesai')->count();
        $tolak_kalab = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('kalab_id', Auth::id());
        })->where('status', 'ditolak')->count();
        $pending_kalab = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('kalab_id', Auth::id());
        })->where('status', 'pending_kalab')->count();
        $pengajuan_terbaru_kalab = PeminjamanLab::whereHas('labManager', function ($query) {
            $query->where('kalab_id', Auth::id());
        })->latest()->take(5)->get();

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

        // Peminjaman per lab (untuk chart bar)
        $peminjaman_per_lab = Lab::withCount('peminjamanLabs')->get();

        // 5 pengajuan terbaru untuk tabel ringkasan admin
        $pengajuan_terbaru = PeminjamanLab::with(['mahasiswa', 'lab'])
            ->latest()
            ->take(5)
            ->get();

        // Peminjaman aktif hari ini (status disetujui & waktu mencakup hari ini)
        $peminjaman_hari_ini = PeminjamanLab::where('status', 'disetujui')
            ->whereDate('waktu_mulai', '<=', now())
            ->whereDate('waktu_selesai', '>=', now())
            ->count();

        // ====================================================
        // Statistik khusus Satpam
        // ====================================================

        // Total presensi yang diajukan hari ini
        $presensi_hari_ini = PresensiLab::whereDate('created_at', today())->count();

        // Presensi menunggu konfirmasi (semua satpam)
        $presensi_menunggu = PresensiLab::whereIn('status_presensi', [
            'menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'
        ])->count();

        // Daftar presensi menunggu (maks 5, untuk tabel dashboard)
        $presensi_list_menunggu = PresensiLab::with(['peminjamanLab.lab', 'mahasiswa', 'satpamMasuk', 'satpamKeluar'])
            ->whereIn('status_presensi', ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Mahasiswa yang sedang di dalam lab (status 'didalam')
        $mahasiswa_didalam = PresensiLab::where('status_presensi', 'didalam')->count();

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
        $presensi_per_lab = Lab::selectRaw('labs.id, labs.nama_lab, COUNT(presensi_lab.id) as presensi_count')
            ->leftJoin('peminjaman_lab', 'labs.id', '=', 'peminjaman_lab.lab_id')
            ->leftJoin('presensi_lab', 'peminjaman_lab.id', '=', 'presensi_lab.peminjaman_lab_id')
            ->groupBy('labs.id', 'labs.nama_lab')
            ->get();

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
            'presensi_menunggu',
            'presensi_list_menunggu',
            'mahasiswa_didalam',
            'mahasiswa_didalam_list',
            'total_konfirmasi_saya',
            'konfirmasi_masuk_saya',
            'konfirmasi_keluar_saya',
            'konfirmasi_saya_hari_ini',
            'riwayat_konfirmasi',
            'presensi_per_lab',
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
