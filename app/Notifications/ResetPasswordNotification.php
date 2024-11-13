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
            $resetUrl = "https://ofx-qrcode.com/resetpassword?token={$this->token}&email={$this->email}";
    
            return (new MailMessage)
                ->subject('Reset Password Notification')
                ->line('You are receiving this email because we received a password reset request for your account.')
                ->action('Reset Password', $resetUrl)
                ->line('If you did not request a password reset, no further action is required.');
        }
    }