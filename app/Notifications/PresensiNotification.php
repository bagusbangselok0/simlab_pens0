<?php

namespace App\Notifications;

use App\Models\PeminjamanLab;
use App\Models\PresensiLab;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PresensiNotification extends Notification
{
    use Queueable;

    protected PeminjamanLab $peminjaman;
    protected string $type;
    protected User $mahasiswa;
    protected PresensiLab $presensi;

    public function __construct(PeminjamanLab $peminjaman, string $type, User $mahasiswa, PresensiLab $presensi)
    {
        $this->peminjaman = $peminjaman;
        $this->type = $type;
        $this->mahasiswa = $mahasiswa;
        $this->presensi = $presensi;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $action = $this->type === 'masuk' ? 'masuk' : 'keluar';
        $labName = $this->peminjaman->lab->nama_lab ?? 'Lab';
        $namaMahasiswa = $this->mahasiswa->nama_asli ?? 'Mahasiswa';

        $data = [
            'title' => 'Presensi Baru Menunggu Konfirmasi',
            'message' => "{$namaMahasiswa} mengajukan presensi {$action} di {$labName}.",
            'url' => route('satpam.presensi'),
            'icon' => 'bi-clock-fill',
            'icon_color' => 'warning',
            'sender_name' => $namaMahasiswa,
            'action' => 'presensi',
            'peminjaman_id' => $this->peminjaman->id,
            'presensi_id' => $this->presensi->id,
        ];

        try {
            app(PushNotificationService::class)->sendToUser(
                $notifiable->id,
                $data['title'],
                $data['message'],
                $data['url'],
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PresensiNotification: Gagal kirim push — ' . $e->getMessage());
        }

        return $data;
    }
}
