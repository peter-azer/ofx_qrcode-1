<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionReminder;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class NotifyExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:notify-expiring';
    protected $description = 'Notify users about expiring subscriptions';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get users with active subscriptions ending in the next 7 days
        $users = User::whereHas('packages', function ($query) {
            $query->where('is_enable', true)
                  ->whereBetween('end_date', [
                      Carbon::now()->startOfDay(),
                      Carbon::now()->addWeek()->endOfDay()
                  ]);
        })->get();

        foreach ($users as $user) {

            if ($user->packages->isNotEmpty()) {

            try {
                // Access the first package's pivot 'end_date' (if multiple, choose the one you need)
                $endDate = $user->packages->first()->pivot->end_date;

                if ($endDate) {
                    // Use notify method to send the notification
                    Mail::to($user->email)->send(new SubscriptionReminder($user));

                    // Log email sent
                    \Log::info('Subscription expiry notification sent to: ' . $user->email);
                } else {
                    \Log::warning('No end date found for user: ' . $user->email);
                }

            } catch (\Exception $e) {
                // Log error if email fails
                \Log::error('Failed to send email to: ' . $user->email . ' | Error: ' . $e->getMessage());
            }
        } else {
            \Log::warning('No active packages found for user: ' . $user->email);
        }
    }

    // Return success response after processing all users
    return response()->json(['message' => 'Subscription reminder emails sent.'], 200);
}
}
