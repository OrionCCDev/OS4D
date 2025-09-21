<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Notification</title>
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
            background: #696cff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .task-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #696cff;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-assigned { background: #e3f2fd; color: #1976d2; }
        .status-accepted { background: #e8f5e8; color: #2e7d32; }
        .status-submitted_for_review { background: #fff3e0; color: #f57c00; }
        .status-approved { background: #e8f5e8; color: #2e7d32; }
        .status-rejected { background: #ffebee; color: #c62828; }
        .status-completed { background: #e8f5e8; color: #2e7d32; }
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
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Task Management System</h1>
        <p>Task Update Notification</p>
    </div>

    <div class="content">
        <h2>Hello {{ $stakeholder->name }},</h2>

        <p>{{ $message }}</p>

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
                        <span class="status-badge status-{{ $task->status }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
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
                    <td style="padding: 8px 0; font-weight: bold;">Completion Notes:</td>
                    <td style="padding: 8px 0;">{{ $task->completion_notes }}</td>
                </tr>
                @endif
                @if($task->approval_notes)
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Approval Notes:</td>
                    <td style="padding: 8px 0;">{{ $task->approval_notes }}</td>
                </tr>
                @endif
                @if($task->rejection_notes)
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Rejection Notes:</td>
                    <td style="padding: 8px 0;">{{ $task->rejection_notes }}</td>
                </tr>
                @endif
            </table>
        </div>

        <p>You can view more details by logging into the task management system.</p>
    </div>

    <div class="footer">
        <p>This is an automated notification from the Task Management System.</p>
        <p>Please do not reply to this email.</p>
    </div>
</body>
</html>
