<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * {@inheritdoc}
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(\App\Console\Commands\ProcessPendingSubscriptions::class)->dailyAt('04:00');
        $schedule->command(\App\Console\Commands\ProcessExpiredSubscriptions::class)->twiceDaily();
    }

    /**
     * {@inheritdoc}
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
