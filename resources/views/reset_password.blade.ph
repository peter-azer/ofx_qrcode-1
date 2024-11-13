// resources/views/emails/reset_password.blade.php

@component('mail::message')
# Password Reset

![Logo]({{ $logoUrl }})

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Thanks,<br>
OFXQRCode
@endcomponent
