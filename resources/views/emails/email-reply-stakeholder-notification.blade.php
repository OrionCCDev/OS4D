<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Reply Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .reply-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 15px 0;
        }
        .task-info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 12px;
            color: #6c757d;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>📧 Email Reply Update</h2>
        <p>Hello {{ $stakeholder->name }},</p>
        <p>There has been a reply to an email related to one of your projects.</p>
    </div>

    <div class="content">
        <h3>Project Information</h3>
        <div class="task-info">
            <p><strong>Project:</strong> {{ $task->project->name ?? 'N/A' }}</p>
            <p><strong>Task:</strong> {{ $task->title }}</p>
            <p><strong>Task ID:</strong> #{{ $task->id }}</p>
            <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $task->status)) }}</p>
        </div>

        <h3>Email Details</h3>
        <p><strong>Original Email Subject:</strong> {{ $originalEmail->subject }}</p>
        <p><strong>Sent By:</strong> {{ $originalEmail->from_email }}</p>
        <p><strong>Sent On:</strong> {{ $originalEmail->sent_at->format('M d, Y \a\t g:i A') }}</p>

        <div class="reply-section">
            <h4>📨 New Reply Received</h4>
            <p><strong>From:</strong> {{ $replyEmail->from_email }}</p>
            <p><strong>Subject:</strong> {{ $replyEmail->subject }}</p>
            <p><strong>Received:</strong> {{ $replyEmail->received_at->format('M d, Y \a\t g:i A') }}</p>
            
            <h5>Reply Content:</h5>
            <div style="background-color: white; padding: 10px; border-radius: 3px; margin: 10px 0;">
                {!! nl2br(e(substr(strip_tags($replyEmail->body), 0, 500))) !!}
                @if(strlen(strip_tags($replyEmail->body)) > 500)
                    <p><em>... (content truncated)</em></p>
                @endif
            </div>
        </div>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ url('/tasks/' . $task->id) }}" class="btn">View Task Details</a>
            <a href="{{ url('/emails/' . $replyEmail->id . '/show') }}" class="btn">View Full Reply</a>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification from the Orion Task Management System.</p>
        <p>If you have any questions, please contact your project manager or the system administrator.</p>
        <p><strong>Orion Contracting Company</strong></p>
    </div>
</body>
</html>
