<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $endDate;

    /**
     * Create a new notification instance.
     */
    public function __construct($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {

            \Log::info('Sending subscription reminder email', ['user_email' => $notifiable->email, 'end_date' => $this->endDate]);

            return (new MailMessage)
             // Temporarily send to this fake email address
            ->subject('Your Subscription is About to Expire')

           
            ->line('Your subscription will expire on ' . $this->endDate->toFormattedDateString() . '.')
            ->line('You have one week left to renew your subscription.')
            ->action('Renew Now', url('/subscription/renew'))
            ->line('Thank you for being with us!');
}
}
