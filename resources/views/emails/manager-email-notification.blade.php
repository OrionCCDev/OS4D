<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation Email Sent Notification</title>
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
            background-color: #2c3e50;
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
            background-color: #e8f4fd;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .task-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .email-details {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .recipient-list {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
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
            margin: 10px 0;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-sent {
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
            <h1>📧 Confirmation Email Sent</h1>
        </div>

        <div class="info-box">
            <strong>Hello {{ $manager->name }},</strong><br>
            A confirmation email has been sent for a task in your project. Here are the details:
        </div>

        <div class="task-details">
            <h3>📋 Task Information</h3>
            <p><strong>Task ID:</strong> #{{ $task->id }}</p>
            <p><strong>Task Title:</strong> {{ $task->title }}</p>
            <p><strong>Project:</strong> {{ $task->project->name ?? 'Unknown Project' }}</p>
            <p><strong>Status:</strong> <span class="status-badge status-sent">On Client/Consultant Review</span></p>
            <p><strong>Due Date:</strong> {{ $task->due_date ? $task->due_date->format('M d, Y') : 'Not set' }}</p>
        </div>

        <div class="email-details">
            <h3>📤 Email Details</h3>
            <p><strong>Sent By:</strong> {{ $sender->name }} ({{ $sender->email }})</p>
            <p><strong>Sent At:</strong> {{ now()->format('M d, Y \a\t g:i A') }}</p>
            <p><strong>Subject:</strong> {{ $emailPreparation->subject }}</p>

            @if(!empty($toEmails))
            <div class="recipient-list">
                <strong>To:</strong> {{ implode(', ', $toEmails) }}
            </div>
            @endif

            @if(!empty($ccEmails))
            <div class="recipient-list">
                <strong>CC:</strong> {{ implode(', ', $ccEmails) }}
            </div>
            @endif

            @if(!empty($emailPreparation->attachments))
            <div class="attachment-info">
                <strong>📎 Attachments:</strong> {{ is_array($emailPreparation->attachments) ? count($emailPreparation->attachments) : 0 }} file(s) attached
            </div>
            @endif
        </div>

        <div class="info-box">
            <h4>📝 Email Content Preview</h4>
            <div style="background-color: white; padding: 15px; border-radius: 4px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;">
                {!! Str::limit(strip_tags($emailPreparation->body), 500) !!}
            </div>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('tasks.show', $task->id) }}" class="btn">View Task Details</a>
        </div>

        <div class="footer">
            <p><strong>Orion Engineering System</strong></p>
            <p>This is an automated notification. The confirmation email has been successfully sent to the client/consultant.</p>
            <p>You will receive another notification when they reply to the email.</p>
            <p>For support, contact: engineering@orion-contracting.com</p>
        </div>
    </div>
</body>
</html>
