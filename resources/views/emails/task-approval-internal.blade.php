<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Approved - {{ $task->title }}</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 12px 12px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 12px 12px;
        }
        .task-details {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-approved { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .priority-low { background: #e8f5e8; color: #2e7d32; }
        .priority-normal { background: #e3f2fd; color: #1976d2; }
        .priority-medium { background: #e1f5fe; color: #0277bd; }
        .priority-high { background: #fff3e0; color: #f57c00; }
        .priority-urgent { background: #ffebee; color: #c62828; }
        .priority-critical { background: #f3e5f5; color: #7b1fa2; }
        .approval-section {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 10px 0;
        }
        .btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Task Approved!</h1>
        <p>Congratulations! Your task has been approved</p>
    </div>

    <div class="content">
        <h2>Hello {{ $user->name }},</h2>

        <p>Great news! Your task <strong>"{{ $task->title }}"</strong> has been approved by {{ $approver->name }}.</p>

        <div class="approval-section">
            <h3>✅ Approval Details</h3>
            <p><strong>Approved by:</strong> {{ $approver->name }} ({{ $approver->role }})</p>
            <p><strong>Approved on:</strong> {{ $task->approved_at ? $task->approved_at->format('M d, Y \a\t g:i A') : now()->format('M d, Y \a\t g:i A') }}</p>
            @if($task->approval_notes)
                <p><strong>Approval Notes:</strong> {{ $task->approval_notes }}</p>
            @endif
        </div>

        <div class="task-details">
            <h3>Task Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 30%;">Title:</td>
                    <td style="padding: 8px 0;">{{ $task->title }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Description:</td>
                    <td style="padding: 8px 0;">{{ $task->description ?: 'No description provided' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Project:</td>
                    <td style="padding: 8px 0;">{{ $task->project->name ?? 'No project assigned' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Assigned To:</td>
                    <td style="padding: 8px 0;">{{ $task->assignee->name ?? 'Unassigned' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Due Date:</td>
                    <td style="padding: 8px 0;">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'Not set' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Status:</td>
                    <td style="padding: 8px 0;">
                        <span class="status-badge status-approved">
                            Approved ✅
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Priority:</td>
                    <td style="padding: 8px 0;">
                        <span class="priority-badge priority-{{ $task->priority }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                </tr>
                @if($task->completion_notes)
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Your Completion Notes:</td>
                    <td style="padding: 8px 0;">{{ $task->completion_notes }}</td>
                </tr>
                @endif
                @if($task->completed_at)
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Completed On:</td>
                    <td style="padding: 8px 0;">{{ $task->completed_at->format('M d, Y \a\t g:i A') }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('tasks/' . $task->id) }}" class="btn">View Task Details</a>
        </div>

        <p>Thank you for your excellent work on this task! Keep up the great performance.</p>
    </div>

    <div class="footer">
        <p>This is an automated notification from the Task Management System.</p>
        <p>Please do not reply to this email.</p>
    </div>
</body>
</html>
