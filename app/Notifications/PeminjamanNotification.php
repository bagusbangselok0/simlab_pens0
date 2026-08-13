<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\PeminjamanLab;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PeminjamanNotification extends Notification
{
    use Queueable;

    protected PeminjamanLab $peminjaman;
    protected string $action;
    protected ?User $sender;
    protected ?string $note;

    /**
     * Create a new notification instance.
     *
     * @param  PeminjamanLab  $peminjaman  Data peminjaman terkait
     * @param  string         $action      Jenis aksi: create, approve_plp, approve_final, reject, cancel_auto
     * @param  User|null      $sender      User yang memicu notifikasi (null untuk sistem)
     * @param  string|null    $note        Catatan tambahan (misal catatan penolakan)
     */
    public function __construct(PeminjamanLab $peminjaman, string $action, ?User $sender = null, ?string $note = null)
    {
        $this->peminjaman = $peminjaman;
        $this->action = $action;
        $this->sender = $sender;
        $this->note = $note;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Tambahkan WhatsApp channel jika diaktifkan
        if (config('services.fonnte.enabled', false)) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = $this->getNotificationData();

        // Kirim browser push notification
        try {
            app(PushNotificationService::class)->sendToUser(
                $notifiable->id,
                $data['title'],
                $data['message'],
                $data['url'],
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PeminjamanNotification: Gagal kirim push — ' . $e->getMessage());
        }

        return [
            'title' => $data['title'],
            'message' => $data['message'],
            'url' => $data['url'],
            'icon' => $data['icon'],
            'icon_color' => $data['icon_color'],
            'sender_name' => $this->sender ? $this->sender->nama_asli : 'Sistem',
            'action' => $this->action,
            'peminjaman_id' => $this->peminjaman->id,
        ];
    }

    /**
     * Get the WhatsApp message representation of the notification.
     *
     * @return string
     */
    public function toWhatsApp(object $notifiable): string
    {
        $lab = $this->peminjaman->lab->nama_lab ?? 'N/A';
        $mahasiswa = $this->peminjaman->mahasiswa;
        $namaMahasiswa = $mahasiswa->nama_asli ?? 'N/A';
        $nrp = $mahasiswa->nrp ?? '-';
        $waktuMulai = $this->peminjaman->waktu_mulai ? $this->peminjaman->waktu_mulai->format('d-m-Y H:i') : '-';
        $waktuSelesai = $this->peminjaman->waktu_selesai ? $this->peminjaman->waktu_selesai->format('d-m-Y H:i') : '-';
        $tujuan = $this->peminjaman->tujuan ?? '-';

        return match ($this->action) {
            'create' => "📋 *SIMLAB - Pengajuan Baru*\n\n"
                . "Halo, terdapat pengajuan peminjaman lab baru:\n"
                . "• *Lab*: {$lab}\n"
                . "• *Pemohon*: {$namaMahasiswa} ({$nrp})\n"
                . "• *Waktu*: {$waktuMulai} s.d {$waktuSelesai}\n"
                . "• *Tujuan*: {$tujuan}\n\n"
                . "Silakan login ke dashboard Simlab untuk memproses persetujuan.",

            'approve_plp' => "📋 *SIMLAB - Persetujuan PLP*\n\n"
                . "Halo, pengajuan peminjaman lab berikut telah *DISETUJUI oleh PLP*:\n"
                . "• *Lab*: {$lab}\n"
                . "• *Pemohon*: {$namaMahasiswa} ({$nrp})\n"
                . "• *Waktu*: {$waktuMulai} s.d {$waktuSelesai}\n\n"
                . "Silakan lakukan persetujuan akhir pada menu approval di dashboard Simlab.",

            'approve_final' => "✅ *SIMLAB - Peminjaman Disetujui*\n\n"
                . "Halo {$namaMahasiswa},\n\n"
                . "Kabar baik! Pengajuan peminjaman Lab *{$lab}* Anda telah *DISETUJUI*.\n"
                . "• *Waktu*: {$waktuMulai} s.d {$waktuSelesai}\n\n"
                . "Silakan unduh atau cetak bukti peminjaman pada menu peminjaman di dashboard Simlab.",

            'reject' => "❌ *SIMLAB - Peminjaman Ditolak*\n\n"
                . "Halo {$namaMahasiswa},\n\n"
                . "Mohon maaf, pengajuan peminjaman Lab *{$lab}* Anda *DITOLAK*.\n"
                . ($this->note ? "• *Catatan Penolakan*: {$this->note}\n\n" : "\n")
                . "Silakan ajukan kembali dengan memperbaiki data sesuai catatan tersebut.",

            'cancel_auto' => "⏰ *SIMLAB - Pembatalan Otomatis*\n\n"
                . "Halo {$namaMahasiswa},\n\n"
                . "Pengajuan peminjaman Lab *{$lab}* Anda telah *DIBATALKAN otomatis oleh sistem* karena tidak ditindaklanjuti oleh petugas dalam waktu 24 jam.\n\n"
                . "Silakan ajukan kembali jika masih diperlukan.",

            default => '',
        };
    }

    /**
     * Generate notification data based on action type.
     *
     * @return array<string, string>
     */
    protected function getNotificationData(): array
    {
        $lab = $this->peminjaman->lab->nama_lab ?? 'N/A';
        $mahasiswa = $this->peminjaman->mahasiswa;
        $namaMahasiswa = $mahasiswa->nama_asli ?? 'N/A';

        return match ($this->action) {
            'create' => [
                'title' => 'Pengajuan Peminjaman Baru',
                'message' => "{$namaMahasiswa} mengajukan peminjaman Lab {$lab}.",
                'url' => route('approval.index'),
                'icon' => 'bi-inbox-fill',
                'icon_color' => 'primary',
            ],
            'approve_plp' => [
                'title' => 'Menunggu Persetujuan Anda',
                'message' => "Peminjaman Lab {$lab} oleh {$namaMahasiswa} telah disetujui PLP, menunggu persetujuan Anda.",
                'url' => route('approval.index'),
                'icon' => 'bi-clock-fill',
                'icon_color' => 'info',
            ],
            'approve_final' => [
                'title' => 'Peminjaman Disetujui!',
                'message' => "Peminjaman Lab {$lab} Anda telah disetujui sepenuhnya.",
                'url' => route('peminjaman.index'),
                'icon' => 'bi-check-circle-fill',
                'icon_color' => 'success',
            ],
            'reject' => [
                'title' => 'Peminjaman Ditolak',
                'message' => "Peminjaman Lab {$lab} Anda ditolak." . ($this->note ? " Catatan: {$this->note}" : ''),
                'url' => route('peminjaman.index'),
                'icon' => 'bi-x-circle-fill',
                'icon_color' => 'danger',
            ],
            'cancel_auto' => [
                'title' => 'Peminjaman Dibatalkan Otomatis',
                'message' => "Peminjaman Lab {$lab} Anda dibatalkan otomatis oleh sistem karena tidak ditindaklanjuti dalam 24 jam.",
                'url' => route('peminjaman.index'),
                'icon' => 'bi-exclamation-triangle-fill',
                'icon_color' => 'warning',
            ],
            default => [
                'title' => 'Notifikasi',
                'message' => 'Ada pembaruan terkait peminjaman lab.',
                'url' => route('dashboard'),
                'icon' => 'bi-bell-fill',
                'icon_color' => 'secondary',
            ],
        };
    }
}
