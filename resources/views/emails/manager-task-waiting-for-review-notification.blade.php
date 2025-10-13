<!DOCTYPE html>
<html>
<head>
    <title>Task Waiting for Client/Consultant Review</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 80%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f4f4f4; padding: 10px; text-align: center; }
        .content { margin-top: 20px; }
        .footer { margin-top: 30px; font-size: 0.8em; color: #777; text-align: center; }
        .button { display: inline-block; padding: 10px 20px; margin-top: 15px; background-color: #ffc107; color: #000; text-decoration: none; border-radius: 5px; }
        .status-badge { display: inline-block; padding: 5px 10px; background-color: #ffc107; color: #000; border-radius: 3px; font-size: 0.9em; font-weight: bold; }
        .info-box { background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0; }
        .warning-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Task Waiting for Client/Consultant Review - Task #{{ $task->id }}</h2>
        </div>
        <div class="content">
            <p>Dear {{ $manager->name }},</p>
            <p>A confirmation email has been <strong>successfully sent</strong> for Task <strong>#{{ $task->id }}: {{ $task->title }}</strong> and the task is now waiting for client/consultant review.</p>

            <div class="status-badge">⏳ Waiting for Client/Consultant Review</div>

            <div class="info-box">
                <h3>📧 Email Sent Successfully</h3>
                <ul>
                    <li><strong>Sent By:</strong> {{ $sender->name }} ({{ $sender->email }})</li>
                    <li><strong>Subject:</strong> {{ $emailPreparation->subject }}</li>
                    <li><strong>To:</strong> {{ implode(', ', $toEmails) }}</li>
                    @if(!empty($ccEmails))
                        <li><strong>Cc:</strong> {{ implode(', ', $ccEmails) }}</li>
                    @endif
                    <li><strong>Sent At:</strong> {{ \Carbon\Carbon::parse($emailPreparation->sent_at)->format('Y-m-d H:i:s') }}</li>
                    @if($emailPreparation->has_attachments)
                        <li><strong>Attachments:</strong> {{ $emailPreparation->attachment_count ?? 0 }} file(s)</li>
                    @endif
                </ul>
            </div>

            <div class="warning-box">
                <h3>⏰ Next Steps - Action Required</h3>
                <p>The task is now in <strong>"On Client/Consultant Review"</strong> status. The system is waiting for:</p>
                <ul>
                    <li><strong>Client Response:</strong> Client needs to review and respond to the confirmation email</li>
                    <li><strong>Consultant Response:</strong> Consultant needs to review and respond to the confirmation email</li>
                </ul>
                <p><strong>Important:</strong> Monitor the task for client/consultant responses and update the approval status accordingly.</p>
            </div>

            <h3>Task Summary:</h3>
            <p>{{ $task->description }}</p>

            <h3>Task Details:</h3>
            <ul>
                <li><strong>Task ID:</strong> #{{ $task->id }}</li>
                <li><strong>Title:</strong> {{ $task->title }}</li>
                <li><strong>Assigned To:</strong> {{ $task->assignee->name ?? 'Unassigned' }}</li>
                <li><strong>Priority:</strong> {{ ucfirst($task->priority ?? 'Normal') }}</li>
                <li><strong>Status:</strong> On Client/Consultant Review</li>
                <li><strong>Created:</strong> {{ $task->created_at->format('Y-m-d H:i:s') }}</li>
            </ul>

            <p>You can view the task details and track its progress by clicking the button below:</p>
            <p>
                <a href="{{ route('tasks.show', $task->id) }}" class="button">View Task Details</a>
            </p>
        </div>
        <div class="footer">
            <p>This is an automated notification from the Orion Engineering System.</p>
            <p><strong>Note:</strong> This task requires your attention as it's waiting for client/consultant responses.</p>
        </div>
    </div>
</body>
</html>
