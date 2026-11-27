<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class BreadcrumbResolver
{
    /**
     * Hasilkan daftar item breadcrumb berdasarkan route aktif atau override manual.
     *
     * Format tiap item:
     * - label: string
     * - url: ?string (null jika grup non-klik atau item halaman aktif terakhir)
     * - is_home: bool (opsional)
     *
     * @param  mixed  $customBreadcrumbs  Array override dari controller/view, atau false untuk menyembunyikan.
     * @param  array<string, mixed>  $context  Konteks opsional (title, user, logbook, dll.)
     * @return array<int, array{label: string, url: ?string, is_home?: bool}>
     */
    public static function resolve(mixed $customBreadcrumbs = null, array $context = []): array
    {
        if ($customBreadcrumbs === false) {
            return [];
        }

        $routeName = (string) ($context['route_name'] ?? Route::currentRouteName() ?? '');

        if ($routeName === '' || $routeName === 'dashboard') {
            return [];
        }

        $dashboardUrl = Route::has('dashboard') ? route('dashboard') : url('/dashboard');
        $homeItem = [
            'label' => 'Dashboard',
            'url' => $dashboardUrl,
            'is_home' => true,
        ];

        if (is_array($customBreadcrumbs) && !empty($customBreadcrumbs)) {
            $normalized = [$homeItem];
            $lastIdx = array_key_last($customBreadcrumbs);
            foreach ($customBreadcrumbs as $idx => $item) {
                if (!is_array($item) || empty($item['label'])) {
                    continue;
                }
                $normalized[] = [
                    'label' => (string) $item['label'],
                    'url' => $idx === $lastIdx ? null : ($item['url'] ?? null),
                ];
            }

            return count($normalized) > 1 ? $normalized : [];
        }

        $title = trim((string) ($context['title'] ?? ''));
        /** @var ?User $user */
        $user = $context['user'] ?? null;
        $roleSlug = strtolower((string) ($user?->role?->slug ?? ''));
        $logbook = $context['logbook'] ?? null;

        $trail = match ($routeName) {
            // Jadwal Lab
            'jadwal-lab.index' => [
                ['label' => 'Jadwal Lab', 'url' => null],
            ],
            'jadwal-lab.preview-show', 'jadwal-lab.preview-import' => [
                ['label' => 'Jadwal Lab', 'url' => static::safeRoute('jadwal-lab.index')],
                ['label' => 'Preview & Konversi Import', 'url' => null],
            ],

            // Data Master (Admin)
            'users.index' => [
                ['label' => 'Data Master', 'url' => null],
                ['label' => 'Users', 'url' => null],
            ],
            'jabatan.index' => [
                ['label' => 'Data Master', 'url' => null],
                ['label' => 'Jabatan', 'url' => null],
            ],
            'lab.index' => [
                ['label' => 'Data Master', 'url' => null],
                ['label' => 'Laboratorium', 'url' => null],
            ],
            'lab_manager.index' => [
                ['label' => 'Data Master', 'url' => null],
                ['label' => 'Penanggung Jawab Lab', 'url' => null],
            ],
            'admin.signatures.index' => [
                ['label' => 'Data Master', 'url' => null],
                ['label' => 'Verifikasi TTD', 'url' => null],
            ],
            'settings.index' => [
                ['label' => 'Data Master', 'url' => null],
                ['label' => 'Pengaturan Sistem', 'url' => null],
            ],

            // Inventaris Lab
            'inventaris.index' => [
                ['label' => 'Inventaris Lab', 'url' => null],
                ['label' => 'Master Inventaris', 'url' => null],
            ],
            'inventaris-ruangan.index' => [
                ['label' => 'Inventaris Lab', 'url' => null],
                ['label' => 'Daftar Inventaris (DIR)', 'url' => null],
            ],

            // Logbook Praktikum & Approval
            'logbook.index' => [
                ['label' => 'Logbook Praktikum', 'url' => null],
            ],
            'logbook.show' => [
                ['label' => 'Logbook Praktikum', 'url' => static::safeRoute('logbook.index')],
                [
                    'label' => (is_object($logbook) && !empty($logbook->nama_mk))
                        ? (string) $logbook->nama_mk
                        : ($title !== '' ? $title : 'Detail Logbook'),
                    'url' => null,
                ],
            ],
            'logbook.approval.index' => [
                ['label' => 'Approval Logbook', 'url' => null],
            ],

            // Peminjaman Ruang & Alat
            'peminjaman.index' => [
                ['label' => 'Peminjaman Lab', 'url' => null],
            ],
            'peminjaman.admin' => [
                ['label' => 'Semua Peminjaman', 'url' => null],
            ],
            'peminjaman-alat.index' => [
                ['label' => 'Peminjaman Alat', 'url' => null],
            ],
            'peminjaman-alat.approval' => [
                ['label' => 'Persetujuan Alat', 'url' => null],
            ],
            'peminjaman-alat.show' => [
                $roleSlug === 'plp'
                    ? ['label' => 'Persetujuan Alat', 'url' => static::safeRoute('peminjaman-alat.approval')]
                    : ['label' => 'Peminjaman Alat', 'url' => static::safeRoute('peminjaman-alat.index')],
                ['label' => $title !== '' ? $title : 'Detail Peminjaman Alat', 'url' => null],
            ],
            'approval.index' => [
                ['label' => 'Approval Lab', 'url' => null],
            ],

            // Presensi
            'presensi.index' => [
                ['label' => 'Presensi Lab', 'url' => null],
            ],
            'presensi.riwayat' => [
                ['label' => 'Presensi Lab', 'url' => static::safeRoute('presensi.index')],
                ['label' => 'Riwayat Presensi', 'url' => null],
            ],
            'presensi.monitoring' => [
                ['label' => 'Monitoring Presensi', 'url' => null],
            ],
            'satpam.presensi' => [
                ['label' => 'Konfirmasi Presensi', 'url' => null],
            ],

            // Profil & Notifikasi
            'profile.index' => [
                ['label' => 'Profil & Akun', 'url' => null],
            ],
            'notifications.index' => [
                ['label' => 'Notifikasi', 'url' => null],
            ],

            // Fallback otomatis untuk halaman baru
            default => $title !== ''
                ? [['label' => $title, 'url' => null]]
                : [],
        };

        if (empty($trail)) {
            return [];
        }

        return array_merge([$homeItem], $trail);
    }

    private static function safeRoute(string $name): ?string
    {
        return Route::has($name) ? route($name) : null;
    }
}
