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
            ->subject('Your Subscription is Ending Soon')
            // ->greeting('Hello, ' . $notifiable->name . '!')
            // ->line('Your subscription is ending on ' . $this->endDate->toFormattedDateString() . '.')
            ->line('You have one week left.')
            // ->action('Renew Now', url('/subscriptions/renew'))
            ->line('Thank you for using our service!');
    }
}
