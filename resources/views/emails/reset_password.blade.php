<!-- resources/views/emails/reset_password.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
</head>
<body>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>Click the button below to reset your password:</p>
    <a href="{{ $resetUrl }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none;">Reset Password</a>
    <p>If you did not request a password reset, no further action is required.</p>
    <p>Thanks, OFXQRCode</p>
</body>
</html>
