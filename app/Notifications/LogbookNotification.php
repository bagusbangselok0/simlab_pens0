<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\LogbookPertemuan;
use App\Models\Setting;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class LogbookNotification extends Notification
{
    use Queueable;

    protected LogbookPertemuan $pertemuan;
    protected string $action;
    protected ?User $sender;
    protected ?string $note;

    /**
     * @param string $action submitted|auto_approved|approved|rejected|revoked
     */
    public function __construct(LogbookPertemuan $pertemuan, string $action, ?User $sender = null, ?string $note = null)
    {
        $this->pertemuan = $pertemuan->loadMissing(['logbook.lab', 'logbook.plp', 'logbook.dosen']);
        $this->action = $action;
        $this->sender = $sender;
        $this->note = $note;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        $waEnabled = Setting::boolean(
            Setting::WA_ENABLED,
            'global',
            (bool) config('services.fonnte.enabled', false)
        );

        if ($waEnabled) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        $data = $this->getNotificationData();

        $pushEnabled = Setting::boolean(Setting::PUSH_ENABLED, 'global', true);
        if ($pushEnabled) {
            try {
                app(PushNotificationService::class)->sendToUser(
                    $notifiable->id,
                    $data['title'],
                    $data['message'],
                    $data['url'],
                );
            } catch (\Throwable $e) {
                Log::error('LogbookNotification: Gagal kirim push — ' . $e->getMessage());
            }
        }

        return [
            'title' => $data['title'],
            'message' => $data['message'],
            'url' => $data['url'],
            'icon' => $data['icon'],
            'icon_color' => $data['icon_color'],
            'sender_name' => $this->sender ? $this->sender->nama_asli : 'Sistem',
            'action' => $this->action,
            'logbook_pertemuan_id' => $this->pertemuan->id,
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $book = $this->pertemuan->logbook;
        $mk = $book->nama_mk ?? 'Praktikum';
        $kelas = $book->kelas ?? '-';
        $minggu = $this->pertemuan->minggu_ke;
        $judul = $this->pertemuan->judul_praktikum;
        $plpName = $book->plp?->nama_asli ?? 'PLP';
        $dosenName = $book->display_dosen_name;

        return match ($this->action) {
            'submitted' => "📘 *SIMLAB - Permintaan TTD Logbook Praktikum*\n\n"
                . "Halo {$dosenName},\n\n"
                . "PLP *{$plpName}* mengajukan pengesahan logbook praktikum:\n"
                . "• *MK / Kelas*: {$mk} ({$kelas})\n"
                . "• *Minggu Ke*: {$minggu} — {$judul}\n\n"
                . "Silakan buka menu *Approval Logbook* di SIMLAB untuk meninjau & menandatangani.",

            'auto_approved' => "⚡ *SIMLAB - Info Auto-Approve Logbook Praktikum*\n\n"
                . "Halo {$dosenName},\n\n"
                . "Atas izin auto-approve yang Anda aktifkan, logbook berikut otomatis disetujui & dibubuhi TTD digital Anda:\n"
                . "• *MK / Kelas*: {$mk} ({$kelas})\n"
                . "• *Minggu Ke*: {$minggu} — {$judul}\n"
                . "• *PLP*: {$plpName}\n\n"
                . "Anda tetap dapat meninjau atau membatalkan (Revoke) melalui menu *Approval Logbook* jika diperlukan.",

            'approved' => "✅ *SIMLAB - Logbook Disetujui Dosen*\n\n"
                . "Halo {$plpName},\n\n"
                . "Logbook *{$mk} ({$kelas})* Minggu ke-{$minggu} telah *DISETUJUI & DITANDATANGANI* oleh *{$dosenName}*.\n"
                . "Dokumen sudah siap dicetak ke format resmi FM. LAB-02.",

            'rejected' => "❌ *SIMLAB - Logbook Perlu Revisi*\n\n"
                . "Halo {$plpName},\n\n"
                . "Logbook *{$mk} ({$kelas})* Minggu ke-{$minggu} dikembalikan oleh *{$dosenName}*.\n"
                . ($this->note ? "• *Catatan*: {$this->note}\n" : '')
                . "Silakan perbaiki dan ajukan kembali.",

            'revoked' => "⚠️ *SIMLAB - TTD Logbook Dibatalkan*\n\n"
                . "Halo {$plpName},\n\n"
                . "TTD untuk logbook *{$mk} ({$kelas})* Minggu ke-{$minggu} dibatalkan oleh *{$dosenName}*.\n"
                . ($this->note ? "• *Alasan*: {$this->note}\n" : '')
                . "Silakan perbaiki dan ajukan ulang.",

            default => '',
        };
    }

    protected function getNotificationData(): array
    {
        $book = $this->pertemuan->logbook;
        $mk = $book->nama_mk ?? 'Praktikum';
        $minggu = $this->pertemuan->minggu_ke;
        $plpName = $book->plp?->nama_asli ?? 'PLP';
        $dosenName = $book->display_dosen_name;

        return match ($this->action) {
            'submitted' => [
                'title' => 'Permintaan TTD Logbook Praktikum',
                'message' => "{$plpName} mengajukan TTD logbook {$mk} Minggu ke-{$minggu}.",
                'url' => route('logbook.approval.index'),
                'icon' => 'bi-journal-check',
                'icon_color' => 'primary',
            ],
            'auto_approved' => [
                'title' => 'Logbook Disetujui Otomatis (Auto-Approve)',
                'message' => "Logbook {$mk} Minggu ke-{$minggu} oleh {$plpName} otomatis memakai TTD Anda.",
                'url' => route('logbook.approval.index', ['tab' => 'riwayat']),
                'icon' => 'bi-lightning-charge-fill',
                'icon_color' => 'info',
            ],
            'approved' => [
                'title' => 'Logbook Praktikum Disetujui',
                'message' => "Logbook {$mk} Minggu ke-{$minggu} telah disetujui {$dosenName}.",
                'url' => route('logbook.show', $book->id),
                'icon' => 'bi-check-circle-fill',
                'icon_color' => 'success',
            ],
            'rejected' => [
                'title' => 'Logbook Praktikum Perlu Revisi',
                'message' => "Logbook {$mk} Minggu ke-{$minggu} ditolak {$dosenName}.",
                'url' => route('logbook.show', $book->id),
                'icon' => 'bi-x-circle-fill',
                'icon_color' => 'danger',
            ],
            'revoked' => [
                'title' => 'TTD Logbook Dibatalkan',
                'message' => "TTD logbook {$mk} Minggu ke-{$minggu} dibatalkan oleh {$dosenName}.",
                'url' => route('logbook.show', $book->id),
                'icon' => 'bi-exclamation-triangle-fill',
                'icon_color' => 'warning',
            ],
            default => [
                'title' => 'Pembaruan Logbook Praktikum',
                'message' => "Status logbook {$mk} Minggu ke-{$minggu} diperbarui.",
                'url' => route('logbook.index'),
                'icon' => 'bi-journal-text',
                'icon_color' => 'secondary',
            ],
        };
    }
}
