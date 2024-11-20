<?php

namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\User;
use App\Notifications\SubscriptionReminderNotification;
use Carbon\Carbon;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            \Log::info('Running subscription reminder scheduler');

            // Get users with active subscriptions ending in the next 7 days
            $users = User::whereHas('packages', function ($query) {
                $query->where('is_enable', true)
                      ->whereBetween('end_date', [
                          Carbon::now()->startOfDay(),
                          Carbon::now()->addWeek()->endOfDay()
                      ]);
            })->get();

            \Log::info('Users found for notification:', ['user_count' => $users->count()]);

            foreach ($users as $user) {
                $userPackage = $user->packages()->wherePivot('is_enable', true)->first();
                $endDate = Carbon::parse($userPackage->pivot->end_date);

                try {
                    $user->notify(new SubscriptionReminderNotification($endDate));
                    \Log::info('Notification sent successfully', ['user_id' => $user->id, 'user_email' => $user->email]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'error_message' => $e->getMessage(),
                    ]);
                }
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
