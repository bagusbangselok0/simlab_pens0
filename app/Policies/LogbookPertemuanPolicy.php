<?php

namespace App\Policies;

use App\Models\LogbookPertemuan;
use App\Models\User;

class LogbookPertemuanPolicy
{
    public function manage(User $user, LogbookPertemuan $pertemuan): bool
    {
        $logbook = $pertemuan->logbook;
        if (!$logbook) {
            return $user->role?->slug === 'admin';
        }

        return (new LogbookPraktikumPolicy())->manage($user, $logbook);
    }

    public function review(User $user, LogbookPertemuan $pertemuan): bool
    {
        if ($user->role?->slug === 'admin') {
            return true;
        }

        $logbook = $pertemuan->logbook;
        if (!$logbook) {
            return false;
        }

        return $user->role?->slug === 'dosen' && (int) $logbook->dosen_id === (int) $user->id;
    }
}
