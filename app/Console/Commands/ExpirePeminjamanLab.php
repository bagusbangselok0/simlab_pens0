<?php

namespace App\Console\Commands;

use App\Models\PeminjamanLab;
use App\Models\PresensiLab;
use App\Notifications\PeminjamanNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpirePeminjamanLab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'peminjaman:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengubah status peminjaman: pending > 24 jam menjadi dibatalkan, disetujui yang sudah lewat waktu selesai menjadi kadaluarsa, presensi belum_hadir menjadi tidak_hadir';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Ubah peminjaman pending yang dibuat lebih dari 24 jam lalu menjadi dibatalkan
        $twentyFourHoursAgo = Carbon::now()->subHours(24);
        $pendingLoans = PeminjamanLab::with('mahasiswa', 'lab')
            ->whereIn('status', ['pending_plp', 'pending_kalab'])
            ->where('created_at', '<', $twentyFourHoursAgo)
            ->get();

        $canceledCount = 0;
        foreach ($pendingLoans as $loan) {
            $loan->update(['status' => 'dibatalkan']);
            // Kirim notifikasi pembatalan otomatis ke Mahasiswa
            if ($loan->mahasiswa) {
                $loan->mahasiswa->notify(new PeminjamanNotification($loan, 'cancel_auto'));
            }
            $canceledCount++;
        }

        $this->info("Jumlah peminjaman pending yang dibatalkan (>24 jam): {$canceledCount}");

        // 2. Ubah peminjaman yang sudah disetujui tapi sudah lewat waktu selesai menjadi kadaluarsa
        $now = Carbon::now();
        $expired = PeminjamanLab::where('status', 'disetujui')
            ->where('waktu_selesai', '<=', $now)
            ->update(['status' => 'kadaluarsa']);

        $this->info("Jumlah peminjaman disetujui yang kadaluarsa (lewat waktu selesai): {$expired}");

        // 3. Ubah presensi 'belum_hadir' menjadi 'tidak_hadir' jika waktu selesai peminjaman sudah lewat
        $tidakHadirCount = PresensiLab::where('status_presensi', 'belum_hadir')
            ->whereHas('peminjamanLab', function ($query) use ($now) {
                $query->where('waktu_selesai', '<=', $now);
            })
            ->update(['status_presensi' => 'tidak_hadir']);

        $this->info("Jumlah presensi belum_hadir yang diubah menjadi tidak_hadir: {$tidakHadirCount}");

        // 4. Ubah presensi yang masih menunggu konfirmasi menjadi 'selesai' jika peminjaman sudah kadaluarsa
        $selesaiCount = PresensiLab::whereIn('status_presensi', ['menunggu_konfirmasi_masuk', 'menunggu_konfirmasi_keluar'])
            ->whereHas('peminjamanLab', function ($query) use ($now) {
                $query->where('waktu_selesai', '<=', $now);
            })
            ->update(['status_presensi' => 'selesai']);

        $this->info("Jumlah presensi menunggu konfirmasi yang diubah menjadi selesai: {$selesaiCount}");

        $totalChanges = $canceledCount + $expired + $tidakHadirCount;
        $this->info("Total perubahan status: {$totalChanges}");

        return Command::SUCCESS;
    }
}

