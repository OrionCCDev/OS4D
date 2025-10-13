<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Email Received - Engineering Inbox</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #e74c3c;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 20px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .email-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .task-details {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .email-content {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            max-height: 300px;
            overflow-y: auto;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 5px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-new {
            background-color: #d4edda;
            color: #155724;
        }
        .attachment-info {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 New Email Received</h1>
        </div>

        <div class="info-box">
            <strong>Hello {{ $manager->name }},</strong><br>
            A new email has been received in the engineering inbox. Please review the details below.
        </div>

        <div class="email-details">
            <h3>📨 Email Information</h3>
            <p><strong>From:</strong> {{ $emailData['from_email'] ?? 'Unknown' }}</p>
            <p><strong>To:</strong> {{ $emailData['to_email'] ?? 'engineering@orion-contracting.com' }}</p>
            <p><strong>Subject:</strong> {{ $emailData['subject'] ?? 'No Subject' }}</p>
            <p><strong>Received:</strong> {{ isset($emailData['date']) ? $emailData['date']->format('M d, Y \a\t g:i A') : now()->format('M d, Y \a\t g:i A') }}</p>
            <p><strong>Message ID:</strong> {{ $emailData['message_id'] ?? 'Unknown' }}</p>

            @if(isset($emailData['attachments']) && !empty($emailData['attachments']))
            <div class="attachment-info">
                <strong>📎 Attachments:</strong> {{ count($emailData['attachments']) }} file(s) attached
                <ul>
                    @foreach($emailData['attachments'] as $attachment)
                    <li>{{ $attachment['filename'] ?? 'Unknown file' }} ({{ $attachment['size'] ?? 0 }} bytes)</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        @if($relatedTask)
        <div class="task-details">
            <h3>🔗 Related Task</h3>
            <p><strong>Task ID:</strong> #{{ $relatedTask->id }}</p>
            <p><strong>Task Title:</strong> {{ $relatedTask->title }}</p>
            <p><strong>Project:</strong> {{ $relatedTask->project->name ?? 'Unknown Project' }}</p>
            <p><strong>Status:</strong> <span class="status-badge status-new">{{ ucfirst(str_replace('_', ' ', $relatedTask->status)) }}</span></p>
            <p><strong>Assigned To:</strong> {{ $relatedTask->assignee->name ?? 'Unassigned' }}</p>
        </div>
        @else
        <div class="info-box">
            <strong>⚠️ No Related Task Found</strong><br>
            This email doesn't appear to be related to any specific task. You may need to manually review and assign it.
        </div>
        @endif

        <div class="email-content">
            <h4>📝 Email Content</h4>
            <div style="white-space: pre-wrap;">{{ $emailData['body'] ?? 'No content available' }}</div>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            @if($relatedTask)
            <a href="{{ route('tasks.show', $relatedTask->id) }}" class="btn">View Related Task</a>
            @endif
            <a href="{{ route('emails.live-monitoring') }}" class="btn btn-danger">View Email Tracker</a>
        </div>

        <div class="footer">
            <p><strong>Orion Engineering System</strong></p>
            <p>This is an automated notification for new emails received in the engineering inbox.</p>
            <p>For support, contact: engineering@orion-contracting.com</p>
        </div>
    </div>
</body>
</html>
