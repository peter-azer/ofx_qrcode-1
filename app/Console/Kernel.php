<?php

namespace App\Console;

use App\Console\Commands\NotifyExpiringSubscriptions; // Import your command class
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule the custom command to run every minute
        $schedule->command('subscriptions:notify-expiring')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        // Automatically load all commands in app/Console/Commands
        $this->load(__DIR__.'/Commands');

        // Include routes/console.php where you can define additional commands
        require base_path('routes/console.php');
    }
}
