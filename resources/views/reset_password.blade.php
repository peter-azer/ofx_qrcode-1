

<!-- resources/views/vendor/notifications/email.blade.php -->

@component('mail::message')
# Password Reset

<!-- Replace the default Laravel logo with your custom logo -->
![Logo](https://backend.ofx-qrcode.com/storage/ofxqr-logo/OFX-QR%20logo.png)

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Thanks,  
OFXQRCode
@endcomponent
