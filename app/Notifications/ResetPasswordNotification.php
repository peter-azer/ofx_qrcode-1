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
     protected $token;
     protected $email;
 
     public function __construct($token, $email)
     {
         $this->token = $token;
         $this->email = $email;
     }
 

     public function via($notifiable)
     {
         return ['mail'];  // Send this notification via email
     }
     public function toMail($notifiable)
     {
         // Custom reset URL with your desired base URL
         $resetUrl = "https://ofx-qrcode.com/resetpassword?token={$this->token}&email={$this->email}";
 
         return (new MailMessage)
             ->subject('Password Reset Request')  // Custom subject
             ->line('You are receiving this email because we received a password reset request for your account.')
             // Custom logo URL and removing default Laravel logo by not including it
             ->line("<img src='https://backend.ofx-qrcode.com/storage/ofxqr-logo/OFX-QR%20logo.png' alt='OFX QR Code Logo' style='max-width: 200px; height: auto; margin-bottom: 20px;' />")
             ->line('Click the button below to reset your password:')
             ->action('Reset Password', $resetUrl)
             ->line('If you did not request a password reset, no further action is required.')
             ->line('Thanks, OFXQRCode');
     }
 }

