<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vapid:generate {--show : Tampilkan keys tanpa menulis ke .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID keys untuk Web Push Notification dan tulis ke .env';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!class_exists(\Minishlink\WebPush\VAPID::class)) {
            $this->error('Package minishlink/web-push tidak ditemukan. Jalankan: composer require minishlink/web-push');
            return self::FAILURE;
        }

        $this->info('Generating VAPID keys...');

        try {
            $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
        } catch (\Exception $e) {
            $this->error('Gagal generate VAPID keys: ' . $e->getMessage());
            return self::FAILURE;
        }

        $publicKey  = $keys['publicKey']  ?? $keys['public']  ?? '';
        $privateKey = $keys['privateKey'] ?? $keys['private'] ?? '';

        if (empty($publicKey) || empty($privateKey)) {
            $this->error('Gagal mendapatkan VAPID keys dari package.');
            return self::FAILURE;
        }

        $this->info('');
        $this->line('<fg=green>VAPID Public Key:</> ');
        $this->line("  $publicKey");
        $this->info('');
        $this->line('<fg=green>VAPID Private Key:</> ');
        $this->line("  $privateKey");
        $this->info('');

        if ($this->option('show')) {
            $this->warn('Mode --show: keys TIDAK ditulis ke .env');
            return self::SUCCESS;
        }

        // Tulis ke .env
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->error('File .env tidak ditemukan!');
            return self::FAILURE;
        }

        $env = file_get_contents($envPath);

        // Replace atau append VAPID_PUBLIC_KEY
        if (str_contains($env, 'VAPID_PUBLIC_KEY=')) {
            $env = preg_replace('/^VAPID_PUBLIC_KEY=.*/m', 'VAPID_PUBLIC_KEY=' . $publicKey, $env);
        } else {
            $env .= "\nVAPID_PUBLIC_KEY=$publicKey";
        }

        // Replace atau append VAPID_PRIVATE_KEY
        if (str_contains($env, 'VAPID_PRIVATE_KEY=')) {
            $env = preg_replace('/^VAPID_PRIVATE_KEY=.*/m', 'VAPID_PRIVATE_KEY=' . $privateKey, $env);
        } else {
            $env .= "\nVAPID_PRIVATE_KEY=$privateKey";
        }

        file_put_contents($envPath, $env);

        $this->info('✅ VAPID keys berhasil ditulis ke .env');
        $this->warn('Jalankan "php artisan config:clear" agar perubahan .env diterapkan.');

        return self::SUCCESS;
    }
}
