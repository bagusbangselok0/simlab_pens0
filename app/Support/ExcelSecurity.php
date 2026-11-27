<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class ExcelSecurity
{
    public const MAX_IMPORT_KB = 5120;
    public const MAX_IMPORT_BYTES = 5 * 1024 * 1024;
    public const MAX_XLSX_UNCOMPRESSED_BYTES = 25 * 1024 * 1024;
    public const MAX_XLSX_ENTRIES = 500;

    /**
     * Cegah Excel Formula / CSV Injection dengan memberi prefix tanda kutip tunggal
     * pada nilai teks yang berpotensi dieksekusi sebagai rumus spreadsheet.
     */
    public static function sanitizeFormula(?string $value): string
    {
        $str = (string) ($value ?? '');

        if ($str === '' || $str === '-') {
            return $str;
        }

        $trimmed = ltrim($str, " \t\r\n");

        if (
            in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)
            || ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@'], true))
        ) {
            return "'" . $str;
        }

        return $str;
    }

    /**
     * Format sel SimpleXLSXGen sebagai raw text yang aman dari formula injection
     * maupun injeksi tag markup (<f>, <a>, <!--).
     */
    public static function rawCell(?string $value, string $fallback = ''): string
    {
        $text = ($value === null || trim($value) === '') ? $fallback : $value;
        $sanitized = self::sanitizeFormula($text);
        $normalized = str_replace(["\r\n", "\r", "\n"], ' ', $sanitized);

        return '<raw>' . htmlspecialchars($normalized, ENT_QUOTES, 'UTF-8') . '</raw>';
    }

    /**
     * Sanitasi nama file hasil export PDF/Excel untuk mencegah path traversal & header injection.
     */
    public static function safeFileName(string $baseName, string $extension): string
    {
        $clean = preg_replace('/[^A-Za-z0-9._-]+/', '_', trim($baseName));
        $clean = trim((string) $clean, '._-') ?: 'export';
        $ext = ltrim((string) preg_replace('/[^A-Za-z0-9]+/', '', $extension), '.');

        return $ext !== '' ? "{$clean}.{$ext}" : $clean;
    }

    /**
     * Validasi ketat file import spreadsheet/CSV (ukuran, ekstensi, MIME, magic bytes, & struktur ZIP XLSX).
     *
     * @param  array<int, string>  $allowedExtensions
     * @throws ValidationException
     */
    public static function validateImportFile(UploadedFile $file, array $allowedExtensions): void
    {
        if (!$file->isValid()) {
            throw ValidationException::withMessages([
                'file' => 'File unggahan tidak valid atau rusak.',
            ]);
        }

        $size = (int) $file->getSize();
        if ($size <= 0 || $size > self::MAX_IMPORT_BYTES) {
            throw ValidationException::withMessages([
                'file' => 'Ukuran file import harus lebih dari 0 byte dan maksimal 5 MB.',
            ]);
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => 'Ekstensi file tidak diizinkan untuk import.',
            ]);
        }

        $realPath = $file->getRealPath();
        if ($realPath === false || !is_readable($realPath)) {
            throw ValidationException::withMessages([
                'file' => 'Berkas unggahan tidak dapat dibaca oleh server.',
            ]);
        }

        $mime = strtolower((string) $file->getMimeType());

        if ($ext === 'xlsx') {
            $allowedXlsxMimes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip',
            ];

            if (!in_array($mime, $allowedXlsxMimes, true)) {
                throw ValidationException::withMessages([
                    'file' => 'Tipe MIME file tidak sesuai dengan dokumen Excel (.xlsx).',
                ]);
            }

            $handle = @fopen($realPath, 'rb');
            $header = $handle ? @fread($handle, 4) : '';
            if ($handle) {
                @fclose($handle);
            }

            if ($header !== "PK\x03\x04") {
                throw ValidationException::withMessages([
                    'file' => 'Signature biner file .xlsx tidak valid.',
                ]);
            }

            if (class_exists(\ZipArchive::class)) {
                $zip = new \ZipArchive();
                if ($zip->open($realPath) !== true) {
                    throw ValidationException::withMessages([
                        'file' => 'Arsip Excel (.xlsx) rusak atau tidak dapat dibuka.',
                    ]);
                }

                try {
                    if ($zip->numFiles <= 0 || $zip->numFiles > self::MAX_XLSX_ENTRIES) {
                        throw ValidationException::withMessages([
                            'file' => 'Jumlah entri di dalam file .xlsx melebihi batas aman.',
                        ]);
                    }

                    if (
                        $zip->locateName('xl/workbook.xml') === false
                        || $zip->locateName('[Content_Types].xml') === false
                    ) {
                        throw ValidationException::withMessages([
                            'file' => 'Struktur internal file .xlsx tidak valid (bukan workbook Excel).',
                        ]);
                    }

                    $totalUncompressed = 0;
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);
                        if ($stat === false) {
                            continue;
                        }

                        $entryName = (string) ($stat['name'] ?? '');
                        if (str_contains($entryName, '..') || str_starts_with($entryName, '/')) {
                            throw ValidationException::withMessages([
                                'file' => 'Nama path di dalam arsip .xlsx mencurigakan.',
                            ]);
                        }

                        $totalUncompressed += (int) ($stat['size'] ?? 0);
                        if ($totalUncompressed > self::MAX_XLSX_UNCOMPRESSED_BYTES) {
                            throw ValidationException::withMessages([
                                'file' => 'Ukuran hasil ekstraksi file .xlsx melebihi batas aman (potensi ZIP bomb).',
                            ]);
                        }
                    }
                } finally {
                    $zip->close();
                }
            }

            return;
        }

        if (in_array($ext, ['csv', 'txt'], true)) {
            $allowedCsvMimes = [
                'text/csv',
                'text/plain',
                'application/csv',
                'application/vnd.ms-excel',
                'text/x-comma-separated-values',
            ];

            if (!in_array($mime, $allowedCsvMimes, true)) {
                throw ValidationException::withMessages([
                    'file' => 'Tipe MIME file CSV/TXT tidak valid.',
                ]);
            }

            $handle = @fopen($realPath, 'rb');
            $head = $handle ? (string) @fread($handle, 1024) : '';
            if ($handle) {
                @fclose($handle);
            }

            if (str_contains($head, "\x00")) {
                throw ValidationException::withMessages([
                    'file' => 'File CSV/TXT mengandung karakter biner terlarang.',
                ]);
            }

            if (
                str_starts_with($head, "PK\x03\x04")
                || str_starts_with($head, 'MZ')
                || str_starts_with($head, "\x7FELF")
                || str_starts_with($head, '%PDF')
            ) {
                throw ValidationException::withMessages([
                    'file' => 'File CSV/TXT terdeteksi sebagai berkas biner/eksekusi yang disamarkan.',
                ]);
            }

            $headNoBom = str_starts_with($head, "\xEF\xBB\xBF") ? substr($head, 3) : $head;
            if (preg_match('/^\s*(<\?php|<script|<!doctype\s+html|<html)/i', $headNoBom)) {
                throw ValidationException::withMessages([
                    'file' => 'File CSV/TXT mengandung skrip atau markup HTML yang tidak diizinkan.',
                ]);
            }
        }
    }
}
