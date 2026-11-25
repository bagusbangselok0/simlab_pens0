<?php

namespace App\Policies;

use App\Models\LogbookPraktikum;
use App\Models\User;

class LogbookPraktikumPolicy
{
    public function view(User $user, LogbookPraktikum $logbook): bool
    {
        if ($user->role?->slug === 'admin') {
            return true;
        }

        if ((int) $logbook->plp_id === (int) $user->id) {
            return true;
        }

        return $user->role?->slug === 'dosen' && (int) $logbook->dosen_id === (int) $user->id;
    }

    public function downloadPdf(User $user, LogbookPraktikum $logbook): bool
    {
        return $this->view($user, $logbook);
    }

    public function manage(User $user, LogbookPraktikum $logbook): bool
    {
        if ($user->role?->slug === 'admin') {
            return true;
        }

        return (int) $logbook->plp_id === (int) $user->id;
    }
}
