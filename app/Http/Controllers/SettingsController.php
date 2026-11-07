<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $title = 'Pengaturan Sistem';
        $labs = Lab::with('labManager.kalab')->orderBy('nama_lab')->get();
        $settings = Setting::query()
            ->where('setting_key', Setting::AUTO_APPROVE_KALAB)
            ->whereIn('scope_key', $labs->map(fn (Lab $lab) => Setting::labScope($lab->id)))
            ->get()
            ->keyBy('scope_key');

        // Pengaturan Peminjaman Alat
        $maxItems       = Setting::int(Setting::MAX_ITEMS_PER_REQUEST, 'global', 5);
        $maxLoanDays    = Setting::int(Setting::MAX_LOAN_DAYS, 'global', 7);
        $maxActiveLoans = Setting::int(Setting::MAX_ACTIVE_LOANS, 'global', 2);
        $allowManual    = Setting::boolean(Setting::ALLOW_MANUAL_INPUT, 'global', true);

        // Pengaturan Gateway WhatsApp
        $waEnabled        = Setting::boolean(Setting::WA_ENABLED, 'global', (bool) config('services.fonnte.enabled', false));
        $waToken          = Setting::get(Setting::WA_TOKEN, 'global', (string) config('services.fonnte.token', ''));
        $waApiUrl         = Setting::get(Setting::WA_API_URL, 'global', 'https://api.fonnte.com/send');
        $waPeminjamanLab  = Setting::boolean(Setting::WA_NOTIF_PEMINJAMAN_LAB, 'global', true);
        $waPeminjamanAlat = Setting::boolean(Setting::WA_NOTIF_PEMINJAMAN_ALAT, 'global', true);

        // Pengaturan Gateway Web Push
        $pushEnabled        = Setting::boolean(Setting::PUSH_ENABLED, 'global', true);
        $vapidSubject       = Setting::get(Setting::VAPID_SUBJECT, 'global', (string) config('services.vapid.subject', 'mailto:admin@simlab.com'));
        $vapidPublicKey     = Setting::get(Setting::VAPID_PUBLIC_KEY, 'global', (string) config('services.vapid.public_key', ''));
        $vapidPrivateKey    = Setting::get(Setting::VAPID_PRIVATE_KEY, 'global', (string) config('services.vapid.private_key', ''));
        $pushPeminjamanLab  = Setting::boolean(Setting::PUSH_NOTIF_PEMINJAMAN_LAB, 'global', true);
        $pushPeminjamanAlat = Setting::boolean(Setting::PUSH_NOTIF_PEMINJAMAN_ALAT, 'global', true);

        // Pengaturan Tema & Loader
        $defaultMode    = Setting::get(Setting::THEME_DEFAULT_MODE, 'global', 'light');
        $enableLoader   = Setting::boolean(Setting::THEME_ENABLE_LOADER, 'global', true);
        $loaderSpeed    = Setting::get(Setting::THEME_LOADER_SPEED, 'global', '3000');
        $accentColor    = Setting::get(Setting::THEME_ACCENT_COLOR, 'global', '#435ebe');
        $sidebarDefault = Setting::get(Setting::THEME_SIDEBAR_DEFAULT, 'global', 'expanded');

        return view('pages.settings.index', compact(
            'title',
            'labs',
            'settings',
            'maxItems',
            'maxLoanDays',
            'maxActiveLoans',
            'allowManual',
            'waEnabled',
            'waToken',
            'waApiUrl',
            'waPeminjamanLab',
            'waPeminjamanAlat',
            'pushEnabled',
            'vapidSubject',
            'vapidPublicKey',
            'vapidPrivateKey',
            'pushPeminjamanLab',
            'pushPeminjamanAlat',
            'defaultMode',
            'enableLoader',
            'loaderSpeed',
            'accentColor',
            'sidebarDefault'
        ));
    }

    public function updateAutoApproveKalab(Request $request, Lab $lab)
    {
        $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        if (!$lab->labManager || !$lab->labManager->kalab) {
            return back()->with('error', 'Laboratorium tersebut belum memiliki Kalab.');
        }

        Setting::updateOrCreate(
            [
                'setting_key' => Setting::AUTO_APPROVE_KALAB,
                'scope_key' => Setting::labScope($lab->id),
            ],
            [
                'value' => $request->boolean('enabled') ? 'true' : 'false',
                'value_type' => 'boolean',
                'description' => 'Otomatis menyetujui peminjaman setelah approval PLP.',
                'is_active' => true,
                'updated_by' => Auth::id(),
            ]
        );

        return redirect()->route('settings.index', ['tab' => 'approval'])
            ->with('success', 'Pengaturan auto-approve Kalab berhasil diperbarui.');
    }

    public function updateAlatSettings(Request $request)
    {
        $request->validate([
            'max_items_per_request' => ['required', 'integer', 'min:1', 'max:50'],
            'max_loan_days' => ['required', 'integer', 'min:1', 'max:90'],
            'max_active_loans' => ['required', 'integer', 'min:1', 'max:20'],
            'allow_manual_input' => ['nullable'],
        ], [
            'max_items_per_request.required' => 'Maksimal alat per pengajuan wajib diisi.',
            'max_items_per_request.min' => 'Maksimal alat minimal 1 item.',
            'max_items_per_request.max' => 'Maksimal alat tidak boleh lebih dari 50 item.',
            'max_loan_days.required' => 'Maksimal durasi peminjaman wajib diisi.',
            'max_loan_days.min' => 'Durasi peminjaman minimal 1 hari.',
            'max_loan_days.max' => 'Durasi peminjaman maksimal 90 hari.',
            'max_active_loans.required' => 'Maksimal peminjaman aktif wajib diisi.',
            'max_active_loans.min' => 'Maksimal peminjaman aktif minimal 1.',
            'max_active_loans.max' => 'Maksimal peminjaman aktif maksimal 20.',
        ]);

        $allowManual = $request->has('allow_manual_input') ? 'true' : 'false';

        $settingsToUpdate = [
            [
                'key' => Setting::MAX_ITEMS_PER_REQUEST,
                'value' => (string) $request->input('max_items_per_request'),
                'type' => 'integer',
                'desc' => 'Maksimal variasi/unit alat yang dapat diajukan dalam 1 kali formulir peminjaman.',
            ],
            [
                'key' => Setting::MAX_LOAN_DAYS,
                'value' => (string) $request->input('max_loan_days'),
                'type' => 'integer',
                'desc' => 'Durasi batas maksimal peminjaman alat (dalam hitungan hari).',
            ],
            [
                'key' => Setting::MAX_ACTIVE_LOANS,
                'value' => (string) $request->input('max_active_loans'),
                'type' => 'integer',
                'desc' => 'Maksimal jumlah pengajuan peminjaman alat aktif per mahasiswa secara simultan.',
            ],
            [
                'key' => Setting::ALLOW_MANUAL_INPUT,
                'value' => $allowManual,
                'type' => 'boolean',
                'desc' => 'Apakah mahasiswa diperbolehkan mengajukan alat melalui opsi Input Manual.',
            ],
        ];

        foreach ($settingsToUpdate as $item) {
            Setting::updateOrCreate(
                [
                    'setting_key' => $item['key'],
                    'scope_key' => 'global',
                ],
                [
                    'value' => $item['value'],
                    'value_type' => $item['type'],
                    'description' => $item['desc'],
                    'is_active' => true,
                    'updated_by' => Auth::id(),
                ]
            );
        }

        return redirect()->route('settings.index', ['tab' => 'alat'])
            ->with('success_alat', 'Pengaturan kebijakan peminjaman alat berhasil disimpan.');
    }

    public function updateNotificationSettings(Request $request)
    {
        $request->validate([
            'wa_api_url'        => ['nullable', 'url'],
            'vapid_subject'     => ['nullable', 'string', 'max:255'],
            'vapid_public_key'  => ['nullable', 'string'],
            'vapid_private_key' => ['nullable', 'string'],
        ], [
            'wa_api_url.url' => 'Format Endpoint URL API WhatsApp tidak valid.',
        ]);

        $settings = [
            // WhatsApp
            [
                'key'   => Setting::WA_ENABLED,
                'value' => $request->has('wa_enabled') ? 'true' : 'false',
                'type'  => 'boolean',
                'desc'  => 'Status aktif/nonaktif notifikasi WhatsApp Gateway.',
            ],
            [
                'key'   => Setting::WA_TOKEN,
                'value' => (string) ($request->input('wa_token') ?? ''),
                'type'  => 'string',
                'desc'  => 'API Token Fonnte untuk WhatsApp Gateway.',
            ],
            [
                'key'   => Setting::WA_API_URL,
                'value' => (string) ($request->input('wa_api_url') ?: 'https://api.fonnte.com/send'),
                'type'  => 'string',
                'desc'  => 'Endpoint URL API WhatsApp Gateway.',
            ],
            [
                'key'   => Setting::WA_NOTIF_PEMINJAMAN_LAB,
                'value' => $request->has('wa_peminjaman_lab') ? 'true' : 'false',
                'type'  => 'boolean',
                'desc'  => 'Kirim notifikasi WhatsApp untuk transaksi Peminjaman Lab.',
            ],
            [
                'key'   => Setting::WA_NOTIF_PEMINJAMAN_ALAT,
                'value' => $request->has('wa_peminjaman_alat') ? 'true' : 'false',
                'type'  => 'boolean',
                'desc'  => 'Kirim notifikasi WhatsApp untuk transaksi Peminjaman Alat.',
            ],

            // Web Push
            [
                'key'   => Setting::PUSH_ENABLED,
                'value' => $request->has('push_enabled') ? 'true' : 'false',
                'type'  => 'boolean',
                'desc'  => 'Status aktif/nonaktif Browser Web Push Notification.',
            ],
            [
                'key'   => Setting::VAPID_SUBJECT,
                'value' => (string) ($request->input('vapid_subject') ?: 'mailto:admin@simlab.com'),
                'type'  => 'string',
                'desc'  => 'Subjek VAPID (mailto:admin@simlab.com).',
            ],
            [
                'key'   => Setting::VAPID_PUBLIC_KEY,
                'value' => (string) ($request->input('vapid_public_key') ?? ''),
                'type'  => 'string',
                'desc'  => 'VAPID Public Key untuk Web Push.',
            ],
            [
                'key'   => Setting::VAPID_PRIVATE_KEY,
                'value' => (string) ($request->input('vapid_private_key') ?? ''),
                'type'  => 'string',
                'desc'  => 'VAPID Private Key untuk Web Push.',
            ],
            [
                'key'   => Setting::PUSH_NOTIF_PEMINJAMAN_LAB,
                'value' => $request->has('push_peminjaman_lab') ? 'true' : 'false',
                'type'  => 'boolean',
                'desc'  => 'Kirim notifikasi Web Push untuk transaksi Peminjaman Lab.',
            ],
            [
                'key'   => Setting::PUSH_NOTIF_PEMINJAMAN_ALAT,
                'value' => $request->has('push_peminjaman_alat') ? 'true' : 'false',
                'type'  => 'boolean',
                'desc'  => 'Kirim notifikasi Web Push untuk transaksi Peminjaman Alat.',
            ],
        ];

        foreach ($settings as $item) {
            Setting::updateOrCreate(
                [
                    'setting_key' => $item['key'],
                    'scope_key'   => 'global',
                ],
                [
                    'value'       => $item['value'],
                    'value_type'  => $item['type'],
                    'description' => $item['desc'],
                    'is_active'   => true,
                    'updated_by'  => Auth::id(),
                ]
            );
        }

        return redirect()->route('settings.index', ['tab' => 'notif'])
            ->with('success_notif', 'Pengaturan Gateway WhatsApp dan Web Push berhasil disimpan.');
    }

    public function generateVapidKeys()
    {
        try {
            $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
            return response()->json([
                'success'    => true,
                'publicKey'  => $keys['publicKey'],
                'privateKey' => $keys['privateKey'],
                'message'    => 'Pasangan VAPID keys baru berhasil digenerate!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate VAPID keys: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testWhatsApp(Request $request)
    {
        $request->validate([
            'target_phone' => ['required', 'string'],
        ], [
            'target_phone.required' => 'Nomor WhatsApp tujuan pengujian wajib diisi.',
        ]);

        $token = Setting::get(Setting::WA_TOKEN, 'global', config('services.fonnte.token'));
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token API Fonnte belum tersimpan di Pengaturan. Masukkan token dan simpan terlebih dahulu.',
            ], 422);
        }

        $apiUrl = Setting::get(Setting::WA_API_URL, 'global', 'https://api.fonnte.com/send');

        // Normalisasi format nomor HP ke 62xxx
        $phone = preg_replace('/[^0-9]/', '', $request->target_phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        $testMsg = "🔔 *UJI COBA GATEWAY WHATSAPP SIMLAB*\n\n"
            . "Halo! Jika pesan ini sampai ke Anda, integrasi WhatsApp Gateway (Fonnte) pada SIMLAB telah *BERHASIL TERKONEKSI* dan berfungsi normal.\n\n"
            . "• *Waktu Pengujian*: " . now()->format('d-m-Y H:i:s') . "\n"
            . "• *Penguji*: " . Auth::user()->nama_asli . "\n\n"
            . "_Pesan ini dikirim secara otomatis melalui fitur Test Gateway SIMLAB._";

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(15)->post($apiUrl, [
                'target'  => $phone,
                'message' => $testMsg,
            ]);

            $json = $response->json();

            if ($response->successful() && ($json['status'] ?? false)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan uji coba berhasil terkirim ke WhatsApp: ' . $phone,
                    'detail'  => $json,
                ]);
            }

            $errMsg = $json['reason'] ?? ($json['message'] ?? 'Respon penolakan dari server Fonnte.');
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $errMsg,
                'detail'  => $json,
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi server WhatsApp Gateway: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testWebPush(Request $request)
    {
        $user = Auth::user();
        $subscriptionCount = \App\Models\PushSubscription::where('user_id', $user->id)->count();

        if ($subscriptionCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Browser Anda belum mengizinkan Web Push. Silakan aktifkan izin notifikasi pada tombol lonceng di pojok kanan atas terlebih dahulu.',
            ], 422);
        }

        $pushService = app(\App\Services\PushNotificationService::class);

        try {
            $pushService->sendToUser(
                $user->id,
                '🔔 Tes Web Push SIMLAB Berhasil!',
                'Konfigurasi gateway VAPID dan Web Push browser Anda aktif dan berjalan dengan baik. Waktu: ' . now()->format('H:i:s'),
                route('settings.index', ['tab' => 'notif'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi push uji coba berhasil dikirim ke perangkat Anda!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim push notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateThemeSettings(Request $request)
    {
        $request->validate([
            'default_mode'    => ['required', 'in:light,dark,system'],
            'enable_loader'   => ['nullable'],
            'loader_speed'    => ['required', 'in:1000,3000,5000'],
            'accent_color'    => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sidebar_default' => ['required', 'in:expanded,collapsed'],
        ], [
            'default_mode.required'    => 'Mode tampilan default wajib dipilih.',
            'loader_speed.required'    => 'Durasi timeout loader wajib dipilih.',
            'accent_color.required'    => 'Warna aksen tema wajib ditentukan.',
            'accent_color.regex'       => 'Format warna tidak valid (contoh format: #435ebe).',
            'sidebar_default.required' => 'Status bawaan sidebar wajib dipilih.',
        ]);

        $settings = [
            [
                'key'   => Setting::THEME_DEFAULT_MODE,
                'value' => $request->input('default_mode'),
                'type'  => 'string',
                'desc'  => 'Mode tampilan bawaan sistem bagi pengguna baru/tamu (light, dark, atau system).',
            ],
            [
                'key'   => Setting::THEME_ENABLE_LOADER,
                'value' => $request->has('enable_loader') ? 'true' : 'false',
                'type'  => 'boolean',
                'desc'  => 'Status aktif/nonaktif animasi global page loader saat transisi halaman.',
            ],
            [
                'key'   => Setting::THEME_LOADER_SPEED,
                'value' => (string) $request->input('loader_speed'),
                'type'  => 'string',
                'desc'  => 'Batas waktu maksimal animasi loader tampil (failsafe timeout dalam milidetik).',
            ],
            [
                'key'   => Setting::THEME_ACCENT_COLOR,
                'value' => strtolower($request->input('accent_color')),
                'type'  => 'string',
                'desc'  => 'Warna aksen utama tampilan antarmuka sistem (primary hex color).',
            ],
            [
                'key'   => Setting::THEME_SIDEBAR_DEFAULT,
                'value' => $request->input('sidebar_default'),
                'type'  => 'string',
                'desc'  => 'Status lebar bawaan sidebar pada tampilan desktop (expanded atau collapsed).',
            ],
        ];

        foreach ($settings as $item) {
            Setting::updateOrCreate(
                [
                    'setting_key' => $item['key'],
                    'scope_key'   => 'global',
                ],
                [
                    'value'       => $item['value'],
                    'value_type'  => $item['type'],
                    'description' => $item['desc'],
                    'is_active'   => true,
                    'updated_by'  => Auth::id(),
                ]
            );
        }

        return redirect()->route('settings.index', ['tab' => 'theme'])
            ->with('success_theme', 'Pengaturan tema dan animasi loader berhasil diperbarui.');
    }
}
