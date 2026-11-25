<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureStorageService
{
    public const USER_DIR = 'signatures';
    public const LOGBOOK_DIR = 'logbook_signatures';

    public function sanitizeFilename(?string $filename): ?string
    {
        if ($filename === null) {
            return null;
        }

        $clean = basename(str_replace('\\', '/', trim($filename)));
        if ($clean === '' || $clean === '.' || $clean === '..' || str_contains($clean, "\0")) {
            return null;
        }

        return $clean;
    }

    public function storeUserSignature(UploadedFile $file, int $userId): string
    {
        $ext = strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'png');
        if (!in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            $ext = 'png';
        }

        $filename = 'sig_' . $userId . '_' . Str::uuid()->toString() . '.' . $ext;
        Storage::disk('local')->putFileAs(self::USER_DIR, $file, $filename);

        return $filename;
    }

    public function storeLogbookBase64Signature(string $base64Data): ?string
    {
        $trimmed = trim($base64Data);
        if ($trimmed === '' || strlen($trimmed) > 700000) {
            return null;
        }

        if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,([A-Za-z0-9+\/=\r\n]+)$/i', $trimmed, $matches)) {
            return null;
        }

        $declaredMime = strtolower($matches[1]);
        $binary = base64_decode(preg_replace('/\s+/', '', $matches[2]), true);
        if ($binary === false || strlen($binary) < 50 || strlen($binary) > (512 * 1024)) {
            return null;
        }

        if (!$this->isValidPngOrJpegBinary($binary, $declaredMime)) {
            return null;
        }

        $ext = $declaredMime === 'png' ? 'png' : 'jpg';
        $filename = 'mhs_' . Str::uuid()->toString() . '.' . $ext;
        Storage::disk('local')->put(self::LOGBOOK_DIR . '/' . $filename, $binary);

        return $filename;
    }

    public function copyUserSignatureToLogbook(?string $userSignatureFilename): ?string
    {
        $source = $this->resolveUserSignaturePath($userSignatureFilename);
        if (!$source) {
            return null;
        }

        $bytes = @file_get_contents($source);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION) ?: 'png');
        if (!in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            $ext = 'png';
        }

        $filename = 'mhs_profile_' . Str::uuid()->toString() . '.' . $ext;
        Storage::disk('local')->put(self::LOGBOOK_DIR . '/' . $filename, $bytes);

        return $filename;
    }

    public function resolveUserSignaturePath(?string $filename): ?string
    {
        return $this->resolveFromCandidates($filename, [
            fn (string $clean) => Storage::disk('local')->path(self::USER_DIR . '/' . $clean),
            fn (string $clean) => storage_path('app/public/' . self::USER_DIR . '/' . $clean),
            fn (string $clean) => public_path('storage/' . self::USER_DIR . '/' . $clean),
        ]);
    }

    public function resolveLogbookSignaturePath(?string $filename): ?string
    {
        return $this->resolveFromCandidates($filename, [
            fn (string $clean) => Storage::disk('local')->path(self::LOGBOOK_DIR . '/' . $clean),
            fn (string $clean) => storage_path('app/public/' . self::LOGBOOK_DIR . '/' . $clean),
            fn (string $clean) => public_path('storage/' . self::LOGBOOK_DIR . '/' . $clean),
        ]);
    }

    public function userSignatureExists(?string $filename): bool
    {
        return $this->resolveUserSignaturePath($filename) !== null;
    }

    public function logbookSignatureExists(?string $filename): bool
    {
        return $this->resolveLogbookSignaturePath($filename) !== null;
    }

    public function deleteUserSignature(?string $filename): void
    {
        $clean = $this->sanitizeFilename($filename);
        if (!$clean) {
            return;
        }

        Storage::disk('local')->delete(self::USER_DIR . '/' . $clean);

        foreach ([
            storage_path('app/public/' . self::USER_DIR . '/' . $clean),
            public_path('storage/' . self::USER_DIR . '/' . $clean),
        ] as $legacyPath) {
            if (is_file($legacyPath)) {
                @unlink($legacyPath);
            }
        }
    }

    public function deleteUserSignatureIfUnused(?string $filename, ?int $excludeUserId = null): void
    {
        $clean = $this->sanitizeFilename($filename);
        if (!$clean) {
            return;
        }

        $usedByOtherUser = DB::table('users')
            ->when($excludeUserId !== null, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->where('signature_path', $clean)
            ->exists();

        if ($usedByOtherUser) {
            return;
        }

        $usedByPeminjamanLab = DB::table('peminjaman_lab')
            ->where('ttd_mahasiswa_file', $clean)
            ->orWhere('ttd_plp_file', $clean)
            ->orWhere('ttd_kalab_file', $clean)
            ->exists();

        if ($usedByPeminjamanLab) {
            return;
        }

        $usedByPeminjamanAlat = DB::table('peminjaman_alats')
            ->where('ttd_mahasiswa_file', $clean)
            ->orWhere('ttd_plp_file', $clean)
            ->exists();

        if ($usedByPeminjamanAlat) {
            return;
        }

        $this->deleteUserSignature($clean);
    }

    public static function userSignatureDataUri(?string $filename): ?string
    {
        $service = app(self::class);
        return $service->fileToDataUri($service->resolveUserSignaturePath($filename));
    }

    public static function logbookSignatureDataUri(?string $filename): ?string
    {
        $service = app(self::class);
        return $service->fileToDataUri($service->resolveLogbookSignaturePath($filename));
    }

    public function fileToDataUri(?string $absolutePath): ?string
    {
        if (!$absolutePath || !is_file($absolutePath) || !is_readable($absolutePath)) {
            return null;
        }

        $bytes = @file_get_contents($absolutePath);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        $mime = $this->detectMimeType($absolutePath);
        return 'data:' . $mime . ';base64,' . base64_encode($bytes);
    }

    public function detectMimeType(string $absolutePath): string
    {
        $detected = @mime_content_type($absolutePath);
        if ($detected && str_starts_with($detected, 'image/')) {
            return $detected;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/png';
    }

    /**
     * @param array<int, callable(string): string> $pathBuilders
     */
    private function resolveFromCandidates(?string $filename, array $pathBuilders): ?string
    {
        $clean = $this->sanitizeFilename($filename);
        if (!$clean) {
            return null;
        }

        foreach ($pathBuilders as $builder) {
            $path = $builder($clean);
            if ($path && is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    private function isValidPngOrJpegBinary(string $binary, string $declaredMime): bool
    {
        if ($declaredMime === 'png') {
            if (!str_starts_with($binary, "\x89PNG\r\n\x1a\n")) {
                return false;
            }
            $expectedImageType = IMAGETYPE_PNG;
        } else {
            if (!str_starts_with($binary, "\xFF\xD8\xFF")) {
                return false;
            }
            $expectedImageType = IMAGETYPE_JPEG;
        }

        $info = @getimagesizefromstring($binary);
        if ($info === false) {
            return false;
        }

        $width = (int) ($info[0] ?? 0);
        $height = (int) ($info[1] ?? 0);

        return $info[2] === $expectedImageType
            && $width >= 1 && $width <= 4000
            && $height >= 1 && $height <= 4000;
    }
}
