<html>
<head>
    <style>
        /* General styling for the email */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
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
            max-width: 200px;
            height: auto;
            display: block;
            margin: 0 auto 20px;
        }

        p {
            margin: 10px 0;
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
        <!-- Display the logo at the beginning -->
        <img src="https://backend.ofx-qrcode.com/storage/ofxqr-logo/OFX-QR%20logo.png"
             alt="OFX QR Logo"
             class="logo">

        <p>Dear {{ $name }},</p>
        <p>This is a reminder that your subscription is set to expire on {{ $endDate->format('F j, Y') }}.</p>
        <p>If you have any questions, please feel free to contact us.</p>
        <p>Best regards,<br>The Subscription Team</p>

        <div class="footer">
            <p>You are receiving this email because you subscribed to OFX QR services.
               If you have any concerns, please <a href="mailto:ofxqrcod@ofx-qrcode.com">contact support</a>.</p>
        </div>
    </div>
</body>
</html>
