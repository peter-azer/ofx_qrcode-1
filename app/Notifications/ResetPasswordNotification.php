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
    
            // Logo URL
            $logoUrl = 'https://backend.ofx-qrcode.com/storage/ofxqr-logo/OFX-QR%20logo.png'; // The logo URL
    
            // Return the email content directly using MailMessage
            return (new MailMessage)
                ->from('no-reply@ofx-qrcode.com', 'OFXQRCode') // Set the sender email and name
                ->subject('Reset Password Notification')
                ->line('You are receiving this email because we received a password reset request for your account.')
                ->line('Click the button below to reset your password:')
                ->action('Reset Password', $resetUrl)
                ->line('If you did not request a password reset, no further action is required.')
                ->line('Thanks,')
                ->line('OFXQRCode')
                ->line("<img src='{$logoUrl}' alt='OFX QR Code Logo' style='max-width: 200px; height: auto;'>"); // Embed logo directly
        }

    }