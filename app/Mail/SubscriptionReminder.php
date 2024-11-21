<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Subscription Expiry Reminder')
                    ->view('emails.subscription_reminder')
                    ->with([
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'endDate' => $this->user->packages->first()->pivot->end_date,
                    ]);
    }
}
