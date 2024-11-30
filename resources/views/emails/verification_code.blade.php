<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        /* General styling for the email */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .logo {
            max-width: 150px;
            height: auto;
            display: block;
            margin: 0 auto 20px;
        }

        p {
            margin: 10px 0;
            font-size: 1rem;
        }

        .verification-code {
            display: inline-block;
            font-size: 1.5em;
            font-weight: bold;
            color: #ffffff;
            background-color: #007BFF;
            padding: 15px 25px;
            border-radius: 8px;
            letter-spacing: 1.5px;
            margin-top: 10px;
        }

        .info-text {
            margin-top: 15px;
            font-size: 1rem;
            color: #555;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #555;
            text-align: center;
        }

        .footer a {
            color: #007BFF;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007BFF;
            color: #fff;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Add the logo -->
        <img src="https://backend.ofx-qrcode.com/storage/ofxqr-logo/OFX-QR%20logo.png"
             alt="OFX QR Logo"
             class="logo">

        <!-- Verification code text -->
        <p><strong>Hi there,</strong></p>
        <p>Your verification code is:</p>
        <p class="verification-code">{{ $code }}</p>

        <p class="info-text">This code will expire in 10 minutes. Please use it promptly to complete your verification process.</p>

        <!-- Support link -->
        <div class="footer">
            <p>If you didn’t request this code, please <a href="mailto:ofxqrcod@ofx-qrcode.com">contact our support team</a>.</p>
        </div>
    </div>
</body>
</html>
