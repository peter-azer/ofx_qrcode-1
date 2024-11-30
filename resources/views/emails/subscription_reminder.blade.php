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

        .warning {
            font-size: 18px;
            font-weight: bold;
            color: #ff3333;
            background-color: #fff3f3;
            border: 1px solid #ffcccc;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
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

        .cta-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
        }

        .cta-button:hover {
            background-color: #0056b3;
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

        <!-- Warning Section -->
        <div class="warning">
            <p><strong>Warning:</strong> Your subscription is set to expire on {{ $endDate->format('F j, Y') }}.</p>
            <p>Please take action before your subscription ends to avoid any disruption of service.</p>
        </div>

        <p>If you have any questions or need assistance, feel free to <a href="mailto:ofxqrcod@ofx-qrcode.com">contact us</a>.</p>

        <p>To renew or manage your subscription, please click the button below:</p>

        <a href="your-renewal-link-here" class="cta-button">Renew Subscription</a>

        <p>Best regards,<br>The Subscription Team</p>

        <div class="footer">
            <p>You are receiving this email because you subscribed to OFX QR services.
               If you have any concerns, please <a href="mailto:ofxqrcod@ofx-qrcode.com">contact support</a>.</p>
        </div>
    </div>
</body>
</html>
