<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\PeminjamanAlat;
use App\Models\Setting;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PeminjamanAlatNotification extends Notification
{
    use Queueable;

    protected PeminjamanAlat $peminjamanAlat;
    protected string $action;
    protected ?User $sender;
    protected ?string $note;

    /**
     * Create a new notification instance.
     *
     * @param  PeminjamanAlat $peminjamanAlat
     * @param  string         $action        create, disetujui, ditolak, sedang_dipinjam, selesai
     * @param  User|null      $sender
     * @param  string|null    $note
     */
    public function __construct(PeminjamanAlat $peminjamanAlat, string $action, ?User $sender = null, ?string $note = null)
    {
        $this->peminjamanAlat = $peminjamanAlat;
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

        $waEnabled = Setting::boolean(
            Setting::WA_ENABLED,
            'global',
            (bool) config('services.fonnte.enabled', false)
        );
        $waAlatEnabled = Setting::boolean(Setting::WA_NOTIF_PEMINJAMAN_ALAT, 'global', true);

        if ($waEnabled && $waAlatEnabled) {
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

        $pushEnabled = Setting::boolean(Setting::PUSH_ENABLED, 'global', true);
        $pushAlatEnabled = Setting::boolean(Setting::PUSH_NOTIF_PEMINJAMAN_ALAT, 'global', true);

        if ($pushEnabled && $pushAlatEnabled) {
            try {
                app(PushNotificationService::class)->sendToUser(
                    $notifiable->id,
                    $data['title'],
                    $data['message'],
                    $data['url'],
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('PeminjamanAlatNotification: Gagal kirim push — ' . $e->getMessage());
            }
        }

        return [
            'title'              => $data['title'],
            'message'            => $data['message'],
            'url'                => $data['url'],
            'icon'               => $data['icon'],
            'icon_color'         => $data['icon_color'],
            'sender_name'        => $this->sender ? $this->sender->nama_asli : 'Sistem',
            'action'             => $this->action,
            'peminjaman_alat_id' => $this->peminjamanAlat->id,
        ];
    }

    /**
     * Format WhatsApp notification message.
     */
    public function toWhatsApp(object $notifiable): string
    {
        $kode = $this->peminjamanAlat->kode_peminjaman;
        $lab = $this->peminjamanAlat->lab->nama_lab ?? 'Laboratorium';
        $mahasiswa = $this->peminjamanAlat->mahasiswa;
        $namaMahasiswa = $mahasiswa->nama_asli ?? 'N/A';
        $waktuPinjam = $this->peminjamanAlat->waktu_pinjam ? $this->peminjamanAlat->waktu_pinjam->format('d-m-Y H:i') : '-';
        $rencanaKembali = $this->peminjamanAlat->rencana_kembali ? $this->peminjamanAlat->rencana_kembali->format('d-m-Y H:i') : '-';
        $itemCount = $this->peminjamanAlat->details->count();

        return match ($this->action) {
            'create' => "🛠️ *SIMLAB - Pengajuan Peminjaman Alat Baru*\n\n"
                . "Halo, terdapat pengajuan peminjaman alat laboratorium baru:\n"
                . "• *No. Tiket*: {$kode}\n"
                . "• *Pemohon*: {$namaMahasiswa}\n"
                . "• *Ruangan/Lab*: {$lab}\n"
                . "• *Jumlah Alat*: {$itemCount} item\n"
                . "• *Waktu Pinjam*: {$waktuPinjam} s.d {$rencanaKembali}\n\n"
                . "Silakan login ke dashboard Simlab untuk memeriksa dan memproses persetujuan.",

            'disetujui' => "✅ *SIMLAB - Peminjaman Alat Disetujui*\n\n"
                . "Halo {$namaMahasiswa},\n\n"
                . "Pengajuan peminjaman alat dengan kode *{$kode}* telah *DISETUJUI oleh PLP*.\n"
                . "• *Ruang/Lab*: {$lab}\n"
                . "• *Jadwal Pengambilan*: {$waktuPinjam}\n\n"
                . "Silakan temui PLP penanggung jawab ruangan untuk proses serah terima alat.",

            'ditolak' => "❌ *SIMLAB - Peminjaman Alat Ditolak*\n\n"
                . "Halo {$namaMahasiswa},\n\n"
                . "Mohon maaf, pengajuan peminjaman alat *{$kode}* telah *DITOLAK* oleh PLP.\n"
                . ($this->note ? "• *Alasan*: {$this->note}\n\n" : "\n")
                . "Silakan cek dashboard Simlab jika ingin mengajukan kembali.",

            'sedang_dipinjam' => "📦 *SIMLAB - Alat Telah Diserahkan*\n\n"
                . "Halo {$namaMahasiswa},\n\n"
                . "Peminjaman alat *{$kode}* berstatus *SEDANG DIPINJAM*.\n"
                . "• *Batas Pengembalian*: {$rencanaKembali}\n\n"
                . "Harap menjaga alat dengan baik dan mengembalikan tepat waktu.",

            'selesai' => "🎉 *SIMLAB - Pengembalian Alat Selesai*\n\n"
                . "Halo {$namaMahasiswa},\n\n"
                . "Peminjaman alat *{$kode}* telah selesai dan seluruh alat telah dikembalikan dalam kondisi tercatat oleh PLP. Terima kasih!",

            default => '',
        };
    }

    /**
     * Generate notification data based on action type.
     */
    protected function getNotificationData(): array
    {
        $kode = $this->peminjamanAlat->kode_peminjaman;
        $lab = $this->peminjamanAlat->lab->nama_lab ?? 'Laboratorium';
        $mahasiswa = $this->peminjamanAlat->mahasiswa;
        $namaMahasiswa = $mahasiswa->nama_asli ?? 'N/A';

        return match ($this->action) {
            'create' => [
                'title'      => 'Pengajuan Peminjaman Alat Baru',
                'message'    => "{$namaMahasiswa} mengajukan peminjaman alat ({$kode}) di {$lab}.",
                'url'        => route('peminjaman-alat.approval'),
                'icon'       => 'bi-tools',
                'icon_color' => 'primary',
            ],
            'disetujui' => [
                'title'      => 'Peminjaman Alat Disetujui',
                'message'    => "Pengajuan peminjaman alat Anda ({$kode}) telah disetujui oleh PLP.",
                'url'        => route('peminjaman-alat.index'),
                'icon'       => 'bi-check-circle-fill',
                'icon_color' => 'success',
            ],
            'ditolak' => [
                'title'      => 'Peminjaman Alat Ditolak',
                'message'    => "Pengajuan peminjaman alat Anda ({$kode}) ditolak oleh PLP.",
                'url'        => route('peminjaman-alat.index'),
                'icon'       => 'bi-x-circle-fill',
                'icon_color' => 'danger',
            ],
            'sedang_dipinjam' => [
                'title'      => 'Alat Sedang Dipinjam',
                'message'    => "Alat untuk peminjaman {$kode} telah diserahkan.",
                'url'        => route('peminjaman-alat.index'),
                'icon'       => 'bi-box-seam',
                'icon_color' => 'info',
            ],
            'selesai' => [
                'title'      => 'Peminjaman Alat Selesai',
                'message'    => "Peminjaman alat {$kode} telah selesai dikembalikan.",
                'url'        => route('peminjaman-alat.index'),
                'icon'       => 'bi-award-fill',
                'icon_color' => 'success',
            ],
            default => [
                'title'      => 'Pemberitahuan Peminjaman Alat',
                'message'    => "Status peminjaman alat {$kode} diperbarui.",
                'url'        => route('peminjaman-alat.index'),
                'icon'       => 'bi-bell-fill',
                'icon_color' => 'secondary',
            ],
        };
    }
}

