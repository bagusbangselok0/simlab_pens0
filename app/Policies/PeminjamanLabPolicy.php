<?php

namespace App\Policies;

use App\Models\LabManager;
use App\Models\PeminjamanLab;
use App\Models\User;

class PeminjamanLabPolicy
{
    public function view(User $user, PeminjamanLab $peminjaman): bool
    {
        $roleSlug = $user->role?->slug;

        if ($roleSlug === 'admin') {
            return true;
        }

        if ($roleSlug === 'mahasiswa') {
            return (int) $peminjaman->mahasiswa_id === (int) $user->id;
        }

        if ($roleSlug === 'satpam') {
            return true;
        }

        if ($roleSlug === 'plp' || (int) $user->jabatan_id === 4) {
            return $this->isPlpOfLab($user, $peminjaman);
        }

        if ($roleSlug === 'dosen') {
            return $user->isKalab() && $this->isKalabOfLab($user, $peminjaman);
        }

        return false;
    }

    public function downloadPdf(User $user, PeminjamanLab $peminjaman): bool
    {
        if (!in_array($peminjaman->status, ['disetujui', 'kadaluarsa', 'selesai'], true)) {
            return false;
        }

        return $this->view($user, $peminjaman);
    }

    public function cancel(User $user, PeminjamanLab $peminjaman): bool
    {
        if ($user->role?->slug === 'admin') {
            return true;
        }

        return $user->role?->slug === 'mahasiswa'
            && (int) $peminjaman->mahasiswa_id === (int) $user->id;
    }

    public function approve(User $user, PeminjamanLab $peminjaman): bool
    {
        $roleSlug = $user->role?->slug;

        if ($roleSlug === 'admin') {
            return true;
        }

        if ($roleSlug === 'plp' || (int) $user->jabatan_id === 4) {
            return $this->isPlpOfLab($user, $peminjaman);
        }

        if ($roleSlug === 'dosen') {
            return $user->isKalab() && $this->isKalabOfLab($user, $peminjaman);
        }

        return false;
    }

    public function reject(User $user, PeminjamanLab $peminjaman): bool
    {
        return $this->approve($user, $peminjaman);
    }

    private function isPlpOfLab(User $user, PeminjamanLab $peminjaman): bool
    {
        $manager = $peminjaman->labManager;
        if ($manager) {
            return (int) $manager->plp_id === (int) $user->id;
        }

        return LabManager::where('lab_id', $peminjaman->lab_id)
            ->where('plp_id', $user->id)
            ->exists();
    }

    private function isKalabOfLab(User $user, PeminjamanLab $peminjaman): bool
    {
        $manager = $peminjaman->labManager;
        if ($manager) {
            return (int) $manager->kalab_id === (int) $user->id;
        }

        return LabManager::where('lab_id', $peminjaman->lab_id)
            ->where('kalab_id', $user->id)
            ->exists();
    }
}
