<!DOCTYPE html>
<html>
<head>
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
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logo {
            max-width: 150px;
            height: auto;
            display: block;
            margin: 0 auto 20px;
        }

        p {
            margin: 10px 0;
        }

        .verification-code {
            font-size: 1.2em;
            font-weight: bold;
            color: #007BFF;
        }

        .footer {
            margin-top: 20px;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Add the logo -->
        <img src="https://backend.ofx-qrcode.com/storage/ofxqr-logo/OFX-QR%20logo.png"
             alt="OFX QR Logo"
             class="logo">

        <p>Your verification code is:</p>
        <p class="verification-code">{{ $code }}</p>
        <p>This code will expire in 10 minutes.</p>

        <div class="footer">
            <p>If you didn’t request this code, please <a href="mailto:ofxqrcod@ofx-qrcode.com">contact our support team</a>.</p>
        </div>
    </div>
</body>
</html>
