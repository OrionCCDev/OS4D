<!DOCTYPE html>
<html>
<head>
    <title>Email Marked as Sent Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 80%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f4f4f4; padding: 10px; text-align: center; }
        .content { margin-top: 20px; }
        .footer { margin-top: 30px; font-size: 0.8em; color: #777; text-align: center; }
        .button { display: inline-block; padding: 10px 20px; margin-top: 15px; background-color: #28a745; color: #ffffff; text-decoration: none; border-radius: 5px; }
        .status-badge { display: inline-block; padding: 5px 10px; background-color: #28a745; color: white; border-radius: 3px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Email Marked as Sent - Task #{{ $task->id }}</h2>
        </div>
        <div class="content">
            <p>Dear {{ $manager->name }},</p>
            <p>The user <strong>{{ $sender->name }}</strong> has marked an email as sent for Task <strong>#{{ $task->id }}: {{ $task->title }}</strong>.</p>

            <div class="status-badge">✓ Email Marked as Sent</div>

            <h3>Email Details:</h3>
            <ul>
                <li><strong>Marked as Sent By:</strong> {{ $sender->name }} ({{ $sender->email }})</li>
                <li><strong>Subject:</strong> {{ $emailPreparation->subject }}</li>
                <li><strong>To:</strong> {{ implode(', ', $toEmails) }}</li>
                @if(!empty($ccEmails))
                    <li><strong>Cc:</strong> {{ implode(', ', $ccEmails) }}</li>
                @endif
                <li><strong>Sent Via:</strong> {{ ucfirst(str_replace('_', ' ', $emailPreparation->sent_via)) }}</li>
                <li><strong>Marked as Sent At:</strong> {{ \Carbon\Carbon::parse($emailPreparation->sent_at)->format('Y-m-d H:i:s') }}</li>
                @if($emailPreparation->has_attachments)
                    <li><strong>Attachments:</strong> {{ $emailPreparation->attachment_count ?? 0 }} file(s)</li>
                @endif
            </ul>

            <h3>Task Summary:</h3>
            <p>{{ $task->description }}</p>

            <h3>Next Steps:</h3>
            <p>The task status has been updated to <strong>"On Client/Consultant Review"</strong>. The system is now waiting for client and/or consultant responses.</p>

            <p>You can view the task details and track its progress by clicking the button below:</p>
            <p>
                <a href="{{ route('tasks.show', $task->id) }}" class="button">View Task Details</a>
            </p>
        </div>
        <div class="footer">
            <p>This is an automated notification from the Orion Engineering System.</p>
        </div>
    </div>
</body>
</html>
