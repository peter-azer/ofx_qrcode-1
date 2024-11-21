<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

class SubscriptionReminderNotification extends Notification
{
    protected $endDate;

    public function __construct($endDate)
    {
        $this->endDate = $endDate;
    }

    public function toMail($notifiable)
    {
        $endDate = Carbon::parse($this->endDate);  // Make sure this is a Carbon instance

        \Log::info('Sending subscription reminder email', [
            'user_email' => $notifiable->email,
            'end_date' => $this->endDate
        ]);

        return (new MailMessage)
            ->subject('Your Subscription is About to Expire')
            ->line('Your subscription will expire on ' . $this->endDate->toFormattedDateString() . '.')
            ->line('You have one week left to renew your subscription.')
            ->action('Renew Now', url('/subscription/renew'))
            ->line('Thank you for being with us!');
    }

    // Optional: If you need to define additional channels, you can do so here
    public function via($notifiable)
    {
        return ['mail']; // Indicates this notification should be sent via email
    }
}
