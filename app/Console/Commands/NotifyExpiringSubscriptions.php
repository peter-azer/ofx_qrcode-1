<?php

namespace App\Console\Commands;

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
            // Send email
            Mail::raw("Your subscription is ending soon!", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Subscription Expiry Notification');
            });
        }

        $this->info('Notifications sent to users with expiring subscriptions.');
    }
}
