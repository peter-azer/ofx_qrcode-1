<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable
{
    use SerializesModels;

    public $resetUrl;

    /**
     * Create a new message instance.
     *
     * @param string $resetUrl
     * @return void
     */
    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl;  // Store the reset URL
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Password Reset Request')  // Set the subject of the email
                    ->view('emails.reset_password')  // Specify the Blade template for the email
                    ->with([
                        'resetUrl' => $this->resetUrl,  // Pass the reset URL to the Blade view
                    ]);
    }
}
