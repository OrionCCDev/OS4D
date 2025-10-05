<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(to right, #0056b3, #003d80);
            padding: 20px;
            text-align: center;
            color: #ffffff;
        }
        .content {
            padding: 20px 30px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 20px;
            text-align: center;
            font-size: 0.9em;
            color: #777777;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Orion Contracting</h1>
            <p>Simple Test Email</p>
        </div>
        <div class="content">
            <p>Dear Recipient,</p>
            <p>This is a simple test email from {{ $senderName }}.</p>

            <div style="border: 1px solid #eeeeee; padding: 15px; border-radius: 5px; background-color: #f9f9f9;">
                {!! nl2br(e($bodyContent)) !!}
            </div>

            <p style="margin-top: 20px; font-size: 0.9em; color: #555555;">
                This is a test email to verify email delivery.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Orion Contracting. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
