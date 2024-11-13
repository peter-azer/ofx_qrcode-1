<!-- resources/views/emails/reset_password.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
    <style>
        /* Optional: Style the email */
        body {
            font-family: Arial, sans-serif;
        }
        .logo {
            display: block;
            margin: 0 auto;
            width: 150px; /* Adjust logo size as needed */
        }
        .reset-button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Display the logo at the beginning -->
    <img src="https://backend.ofx-qrcode.com/storage/ofxqr-logo/OFX-QR%20logo.png" alt="OFX QR Logo" class="logo">

    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>Click the button below to reset your password:</p>
    <a href="{{ $resetUrl }}" class="reset-button">Reset Password</a>
    <p>If you did not request a password reset, no further action is required.</p>
    <p>Thanks, OFXQRCode</p>
</body>
</html>
