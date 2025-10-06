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
        .header img {
            max-width: 150px;
            height: auto;
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
        .button {
            display: inline-block;
            background-color: #0056b3;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .details-table th, .details-table td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        .details-table th {
            background-color: #f2f2f2;
        }
        .gmail-notice {
            background-color: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            @if(isset($message) && $message->embed(public_path('uploads/logo-blue.webp')))
                <img src="{{ $message->embed(public_path('uploads/logo-blue.webp')) }}" alt="Orion Contracting Logo" width="150">
            @else
                <img src="{{ asset('uploads/logo-blue.webp') }}" alt="Orion Contracting Logo" width="150">
            @endif
            <h1>Orion Contracting</h1>
        </div>
        <div class="content">
            <p>Dear Recipient,</p>
            <p>You have received a new email from {{ $senderName }}.</p>

            <div class="gmail-notice">
                <strong>📧 Sent via Gmail:</strong> This email was sent from {{ $senderName }}'s Gmail account ({{ $senderEmail }}) through the Orion Contracting system.
            </div>

            <div class="details-table">
                <table>
                    <tr>
                        <th>From:</th>
                        <td>{{ $senderName }} ({{ $senderEmail }})</td>
                    </tr>
                    <tr>
                        <th>To:</th>
                        <td>{{ implode(', ', $toRecipients) }}</td>
                    </tr>
                    <tr>
                        <th>Subject:</th>
                        <td>{{ $subject }}</td>
                    </tr>
                </table>
            </div>

            <p><strong>Message:</strong></p>
            <div style="border: 1px solid #eeeeee; padding: 15px; border-radius: 5px; background-color: #f9f9f9;">
                {!! nl2br(e($bodyContent)) !!}
            </div>

            <p style="margin-top: 20px; font-size: 0.9em; color: #555555;">
                This email was sent from {{ $senderName }}'s Gmail account and automatically CC'd to engineering@orion-contracting.com for record keeping.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Orion Contracting. All rights reserved.</p>
            <p>123 Construction St, Building 456, City, Country</p>
        </div>
    </div>
</body>
</html>
