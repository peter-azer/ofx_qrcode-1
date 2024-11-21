<html>
<body>

    <!-- Display the logo at the beginning -->
    <img src="https://backend.ofx-qrcode.com/storage/ofxqr-logo/OFX-QR%20logo.png" alt="OFX QR Logo" class="logo">
    <p>Dear {{ $name }},</p>
    <p>This is a reminder that your subscription is set to expire on {{ $endDate->format('F j, Y') }}.</p>
    <p>If you have any questions, please feel free to contact us.</p>
    <p>Best regards,<br>The Subscription Team</p>
</body>
</html>
