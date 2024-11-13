<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;


/**
 * Create a new notification instance.
 */
class ResetPasswordNotification extends Notification
{


    use Queueable;
    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Custom reset URL with your desired base URL
        $resetUrl = "https://ofx-qrcode.com/resetpassword?token={$this->token}&email={$this->email}";

  

        return (new MailMessage)
        ->subject('Password Reset Request')  // You can change the subject here
        ->line('You are receiving this email because we received a password reset request for your account.')
        // Remove the default logo and home link by not including this
     
        ->line('Click the button below to reset your password:')
        // Set your custom logo URL here
 
        ->action('Reset Password', $resetUrl)
        ->line('If you did not request a password reset, no further action is required.')
        ->line('Thanks, OFXQRCode');
}
}
