<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionReminder;

class NotifyExpiringSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:notify-expiring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users about subscriptions expiring within the next 7 days';

    /**
     * Execute the console command.
     *
     * @return void
     */
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
                        // Ensure the $endDate is a Carbon instance to avoid issues with format()
                        $endDate = Carbon::parse($endDate);

                        // Send the subscription reminder email
                        Mail::to($user->email)->send(new SubscriptionReminder($user));

                        // Log email sent
                        \Log::info('Subscription expiry notification sent to: ' . $user->email . ' for package expiring on ' . $endDate->format('F j, Y'));
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

        // Return success exit code (0) for successful execution
        $this->info('Subscription reminder emails sent successfully.');
        return 0;  // Return the exit code
    }
}
