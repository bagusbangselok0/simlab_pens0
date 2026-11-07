<?php

namespace App\Channels;

use App\Models\Setting;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Send the given notification via WhatsApp (Fonnte API).
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Jika fitur WA dinonaktifkan, skip pengiriman (Setting DB atau .env)
        $waEnabled = Setting::boolean(
            Setting::WA_ENABLED,
            'global',
            (bool) config('services.fonnte.enabled', false)
        );

        if (!$waEnabled) {
            return;
        }

        // Ambil nomor HP dari notifiable (User)
        $phoneNumber = $notifiable->no_hp;

        if (empty($phoneNumber)) {
            Log::warning('WhatsAppChannel: Nomor HP kosong untuk user ID ' . $notifiable->id);
            return;
        }

        // Format nomor ke format internasional Indonesia
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        // Ambil pesan dari notification
        $message = $notification->toWhatsApp($notifiable);

        if (empty($message)) {
            return;
        }

        $token = Setting::get(Setting::WA_TOKEN, 'global', config('services.fonnte.token'));

        if (empty($token)) {
            Log::error('WhatsAppChannel: FONNTE_TOKEN belum dikonfigurasi di Pengaturan Gateway atau .env');
            return;
        }

        $apiUrl = Setting::get(Setting::WA_API_URL, 'global', 'https://api.fonnte.com/send');

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post($apiUrl, [
                'target' => $phoneNumber,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('WhatsAppChannel: Pesan berhasil dikirim ke ' . $phoneNumber);
            } else {
                Log::error('WhatsAppChannel: Gagal mengirim pesan ke ' . $phoneNumber . '. Response: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('WhatsAppChannel: Exception saat mengirim pesan - ' . $e->getMessage());
        }
    }

    /**
     * Format nomor telepon ke format internasional Indonesia (62xxx).
     *
     * @param  string  $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Hapus karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ganti awalan 0 dengan 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Jika belum diawali 62, tambahkan
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
