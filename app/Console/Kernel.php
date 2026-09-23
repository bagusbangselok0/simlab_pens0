<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Periksa peminjaman yang melewati batas waktu setiap menit.
        $schedule->command('peminjaman:expire')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Hapus notifikasi yang sudah lebih dari 10 hari setiap hari pukul 02:00
        $schedule->command('notifications:prune')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
