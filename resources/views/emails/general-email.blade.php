<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .email-body {
            padding: 30px;
        }
        .email-content {
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }
        .sender-info {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .sender-name {
            font-weight: bold;
            color: #1976d2;
        }
        .recipients {
            background-color: #f3e5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #9c27b0;
        }
        .recipients-title {
            font-weight: bold;
            color: #7b1fa2;
            margin-bottom: 10px;
        }
        .recipient-list {
            margin: 0;
            padding-left: 20px;
        }
        .recipient-item {
            margin-bottom: 5px;
        }
        .cc-note {
            background-color: #fff3e0;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #ff9800;
            font-size: 14px;
            color: #e65100;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="{{ asset('uploads/logo-blue.webp') }}" alt="Orion Contracting Logo" class="logo">
            <h1 class="company-name">Orion Contracting</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <!-- Sender Information -->
            <div class="sender-info">
                <div class="sender-name">From: {{ $sender->name }}</div>
                <div style="color: #666; font-size: 14px;">{{ $sender->email }}</div>
            </div>

            <!-- Recipients Information -->
            @if(!empty($recipients))
            <div class="recipients">
                <div class="recipients-title">To:</div>
                <ul class="recipient-list">
                    @foreach($recipients as $recipient)
                    <li class="recipient-item">{{ $recipient }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Email Content -->
            <div class="email-content">
                {!! nl2br($body) !!}
            </div>

            <!-- CC Note -->
            <div class="cc-note">
                <strong>Note:</strong> This email was automatically CC'd to engineering@orion-contracting.com for record keeping.
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Orion Contracting</strong></p>
            <p>Professional Construction Services</p>
            <p>Email sent via Orion Task Management System</p>
        </div>
    </div>
</body>
</html>
