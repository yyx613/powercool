<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('app:expire-quotation')->dailyAt('00:01')->evenInMaintenanceMode();
        $schedule->command('app:refresh-tik-tok-token')->everyThreeHours();
        $schedule->command('app:refresh-lazada-token')->everyThreeHours();
        $schedule->command('app:refresh-shopee-token')->everyThreeHours();
        $schedule->command('app:prompt-task')->everyFiveMinutes()->withoutOverlapping()->evenInMaintenanceMode();
        $schedule->command('app:service-reminder')->dailyAt(1)->evenInMaintenanceMode();
        $schedule->command('app:vehicle-service-reminder')->dailyAt(1)->evenInMaintenanceMode();
        $schedule->command('app:check-e-invoice-status')->everyThreeHours();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
