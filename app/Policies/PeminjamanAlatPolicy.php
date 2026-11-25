<?php

namespace App\Policies;

use App\Models\LabManager;
use App\Models\PeminjamanAlat;
use App\Models\User;

class PeminjamanAlatPolicy
{
    public function view(User $user, PeminjamanAlat $peminjamanAlat): bool
    {
        $roleSlug = $user->role?->slug;

        if ($roleSlug === 'admin') {
            return true;
        }

        if ($roleSlug === 'mahasiswa') {
            return (int) $peminjamanAlat->mahasiswa_id === (int) $user->id;
        }

        if ($roleSlug === 'plp' || (int) $user->jabatan_id === 4) {
            return $this->canPlpManage($user, $peminjamanAlat);
        }

        if ($roleSlug === 'dosen') {
            return $user->isKalab()
                && $peminjamanAlat->lab_id !== null
                && LabManager::where('lab_id', $peminjamanAlat->lab_id)
                    ->where('kalab_id', $user->id)
                    ->exists();
        }

        return false;
    }

    public function downloadPdf(User $user, PeminjamanAlat $peminjamanAlat): bool
    {
        if (!in_array($peminjamanAlat->status, ['disetujui', 'sedang_dipinjam', 'selesai'], true)) {
            return false;
        }

        return $this->view($user, $peminjamanAlat);
    }

    public function cancel(User $user, PeminjamanAlat $peminjamanAlat): bool
    {
        if ($user->role?->slug === 'admin') {
            return true;
        }

        return $user->role?->slug === 'mahasiswa'
            && (int) $peminjamanAlat->mahasiswa_id === (int) $user->id;
    }

    public function manageApproval(User $user, PeminjamanAlat $peminjamanAlat): bool
    {
        $roleSlug = $user->role?->slug;

        if ($roleSlug === 'admin') {
            return true;
        }

        if ($roleSlug === 'plp' || (int) $user->jabatan_id === 4) {
            return $this->canPlpManage($user, $peminjamanAlat);
        }

        return false;
    }

    private function canPlpManage(User $user, PeminjamanAlat $peminjamanAlat): bool
    {
        if ($peminjamanAlat->plp_id !== null) {
            return (int) $peminjamanAlat->plp_id === (int) $user->id;
        }

        $managedLabIds = LabManager::where('plp_id', $user->id)
            ->pluck('lab_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($managedLabIds)) {
            return false;
        }

        if ($peminjamanAlat->lab_id !== null) {
            if (in_array((int) $peminjamanAlat->lab_id, $managedLabIds, true)) {
                return true;
            }

            $labHasPlp = LabManager::where('lab_id', $peminjamanAlat->lab_id)
                ->whereNotNull('plp_id')
                ->exists();

            if (!$labHasPlp && $peminjamanAlat->details()->where('is_manual', true)->exists()) {
                return true;
            }
        } elseif ($peminjamanAlat->details()->where('is_manual', true)->exists()) {
            return true;
        }

        return $peminjamanAlat->details()
            ->whereHas('inventarisRuangan', fn ($q) => $q->whereIn('lab_id', $managedLabIds))
            ->exists();
    }
}
