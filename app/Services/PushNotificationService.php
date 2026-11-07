<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\Setting;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Kirim push notification ke satu user (semua device-nya).
     *
     * @param  int    $userId
     * @param  string $title
     * @param  string $message
     * @param  string $url
     * @param  string $icon  Path ke icon (relatif dari public/)
     * @return void
     */
    public function sendToUser(int $userId, string $title, string $message, string $url = '/', string $icon = '/images/logo/SIMLAB_logo1.png'): void
    {
        // Cek apakah push notification diaktifkan
        $pushEnabled = Setting::boolean(Setting::PUSH_ENABLED, 'global', true);
        if (!$pushEnabled) {
            return;
        }

        $publicKey  = Setting::get(Setting::VAPID_PUBLIC_KEY, 'global', config('services.vapid.public_key'));
        $privateKey = Setting::get(Setting::VAPID_PRIVATE_KEY, 'global', config('services.vapid.private_key'));
        $subject    = Setting::get(Setting::VAPID_SUBJECT, 'global', config('services.vapid.subject', 'mailto:admin@simlab.com'));

        if (empty($publicKey) || empty($privateKey)) {
            Log::warning('PushNotificationService: VAPID keys belum dikonfigurasi di Pengaturan Gateway atau .env, push notification dilewati.');
            return;
        }

        $subscriptions = PushSubscription::where('user_id', $userId)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title'   => $title,
            'message' => $message,
            'url'     => $url,
            'icon'    => $icon,
        ]);

        try {
            $auth = [
                'VAPID' => [
                    'subject'    => $subject,
                    'publicKey'  => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ];

            $webPush = new WebPush($auth);
            $webPush->setReuseVAPIDHeaders(true);

            foreach ($subscriptions as $sub) {
                try {
                    $subscription = Subscription::create([
                        'endpoint'        => $sub->endpoint,
                        'keys'            => [
                            'p256dh' => $sub->public_key,
                            'auth'   => $sub->auth_token,
                        ],
                        'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
                    ]);

                    $webPush->queueNotification($subscription, $payload);
                } catch (\Exception $e) {
                    Log::error('PushNotificationService: Gagal queue push untuk subscription #' . $sub->id . ' — ' . $e->getMessage());
                }
            }

            foreach ($webPush->flush() as $report) {
                if (!$report->isSuccess()) {
                    $endpoint = $report->getRequest()->getUri()->__toString();
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint', $endpoint)->delete();
                        Log::info('PushNotificationService: Subscription expired dihapus — ' . substr($endpoint, 0, 60) . '...');
                    } else {
                        Log::warning('PushNotificationService: Push gagal - ' . $report->getReason());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('PushNotificationService: Exception saat send push — ' . $e->getMessage());
        }
    }

    /**
     * Kirim push ke multiple users sekaligus.
     *
     * @param  array  $userIds
     * @param  string $title
     * @param  string $message
     * @param  string $url
     * @return void
     */
    public function sendToUsers(array $userIds, string $title, string $message, string $url = '/'): void
    {
        foreach ($userIds as $userId) {
            $this->sendToUser($userId, $title, $message, $url);
        }
    }
}
