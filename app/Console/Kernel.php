<?php

namespace App\Console;

use App\Models\User;
use App\Notifications\SubscriptionReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {
            $users = User::whereHas('packages', function ($query) {
                $query->where('is_enable', true)
                      ->where('end_date', Carbon::now()->addWeek()->startOfDay());
            })->get();

            foreach ($users as $user) {
                $userPackage = $user->packages()->wherePivot('is_enable', true)->first();
                $endDate = Carbon::parse($userPackage->pivot->end_date);

                $user->notify(new SubscriptionReminderNotification($endDate));
            }
        })->everyMinute();
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
