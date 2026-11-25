<?php

namespace App\Console\Commands;

use App\Services\SignatureStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateSignaturesToPrivate extends Command
{
    protected $signature = 'signatures:migrate-private {--keep-legacy : Pertahankan file lama di folder public setelah disalin}';

    protected $description = 'Memindahkan file tanda tangan dari public/storage ke storage/app/private';

    public function handle(): int
    {
        $keepLegacy = (bool) $this->option('keep-legacy');
        $disk = Storage::disk('local');

        $mappings = [
            SignatureStorageService::USER_DIR => [
                storage_path('app/public/' . SignatureStorageService::USER_DIR),
                public_path('storage/' . SignatureStorageService::USER_DIR),
            ],
            SignatureStorageService::LOGBOOK_DIR => [
                storage_path('app/public/' . SignatureStorageService::LOGBOOK_DIR),
                public_path('storage/' . SignatureStorageService::LOGBOOK_DIR),
            ],
        ];

        $movedCount = 0;

        foreach ($mappings as $targetDir => $sourceDirs) {
            $disk->makeDirectory($targetDir);

            foreach ($sourceDirs as $sourceDir) {
                if (!is_dir($sourceDir)) {
                    continue;
                }

                foreach (scandir($sourceDir) ?: [] as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }

                    $sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $entry;
                    if (!is_file($sourcePath)) {
                        continue;
                    }

                    $targetRelative = $targetDir . '/' . $entry;
                    if (!$disk->exists($targetRelative)) {
                        $bytes = @file_get_contents($sourcePath);
                        if ($bytes !== false) {
                            $disk->put($targetRelative, $bytes);
                            $movedCount++;
                        }
                    }

                    if (!$keepLegacy && is_file($sourcePath)) {
                        @unlink($sourcePath);
                    }
                }
            }
        }

        $this->info("Migrasi TTD ke private storage selesai ({$movedCount} file disalin ke storage/app/private).");

        return self::SUCCESS;
    }
}
