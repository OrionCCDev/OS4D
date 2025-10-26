<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Project Report - {{ $project->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1c3644;
            padding-bottom: 20px;
        }

        .company-logo {
            width: 210px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .project-title {
            font-size: 24px;
            font-weight: bold;
            color: #1c3644;
            margin-bottom: 10px;
        }

        .project-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1c3644;
            margin-bottom: 15px;
            padding: 8px 12px;
            background: linear-gradient(135deg, #1c3644, #2c5f6f);
            color: white;
            border-radius: 4px;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stats-row {
            display: table-row;
        }

        .stats-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }

        .stats-value {
            font-size: 18px;
            font-weight: bold;
            color: #1c3644;
        }

        .stats-label {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table th,
        .info-table td {
            padding: 8px 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .info-table th {
            background: #1c3644;
            color: white;
            font-weight: bold;
        }

        .info-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .task-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }

        .task-table th,
        .task-table td {
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .task-table th {
            background: #1c3644;
            color: white;
            font-weight: bold;
            font-size: 8px;
        }

        .task-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .task-separator {
            margin: 15px 0;
            padding: 10px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 4px solid #1c3644;
            border-radius: 0 4px 4px 0;
        }

        .task-number {
            display: inline-block;
            background: #1c3644;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed { background: #d4edda; color: #155724; }
        .status-in_progress { background: #fff3cd; color: #856404; }
        .status-pending { background: #d1ecf1; color: #0c5460; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-approved { background: #d4edda; color: #155724; }

        .priority-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .priority-high { background: #f8d7da; color: #721c24; }
        .priority-medium { background: #fff3cd; color: #856404; }
        .priority-low { background: #d1ecf1; color: #0c5460; }

        .history-item {
            margin: 8px 0;
            padding: 8px;
            background: #f8f9fa;
            border-left: 3px solid #1c3644;
            border-radius: 0 4px 4px 0;
        }

        .history-title {
            font-weight: bold;
            color: #1c3644;
            font-size: 9px;
        }

        .history-meta {
            font-size: 8px;
            color: #666;
            margin-top: 4px;
        }

        .team-member {
            display: inline-block;
            width: 48%;
            margin: 5px 1%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f8f9fa;
        }

        .member-name {
            font-weight: bold;
            color: #1c3644;
            margin-bottom: 5px;
        }

        .member-stats {
            font-size: 8px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="company-logo">
            <img src="{{ public_path('DAssets/logo-blue.webp') }}" alt="Company Logo" style="width: 210px; height: auto;">
        </div>
        <div class="project-title">{{ $project->name }}</div>
        <div class="project-subtitle">Project Code: {{ $project->short_code }}</div>
        <div class="project-subtitle">Status: {{ ucfirst(str_replace('_', ' ', $project->status)) }}</div>
        <div class="project-subtitle">Generated: {{ $exportDate->format('F j, Y \a\t g:i A') }}</div>
    </div>

    <!-- Project Overview Section -->
    <div class="section">
        <div class="section-title">📊 Project Overview</div>

        <table class="info-table">
            <tr>
                <th style="width: 30%;">Project Information</th>
                <th style="width: 70%;">Details</th>
            </tr>
            <tr>
                <td><strong>Project Name</strong></td>
                <td>{{ $project->name }}</td>
            </tr>
            <tr>
                <td><strong>Short Code</strong></td>
                <td>{{ $project->short_code }}</td>
            </tr>
            <tr>
                <td><strong>Description</strong></td>
                <td>{{ $project->description ?? 'No description provided' }}</td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td>{{ ucfirst(str_replace('_', ' ', $project->status)) }}</td>
            </tr>
            <tr>
                <td><strong>Priority</strong></td>
                <td>{{ ucfirst($project->priority ?? 'Normal') }}</td>
            </tr>
            <tr>
                <td><strong>Owner</strong></td>
                <td>{{ $project->owner->name ?? 'Not assigned' }}</td>
            </tr>
            <tr>
                <td><strong>Created By</strong></td>
                <td>{{ $project->owner->name ?? 'Unknown' }}</td>
            </tr>
            <tr>
                <td><strong>Start Date</strong></td>
                <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('F j, Y') : 'Not set' }}</td>
            </tr>
            <tr>
                <td><strong>End Date</strong></td>
                <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('F j, Y') : 'Not set' }}</td>
            </tr>
            <tr>
                <td><strong>Duration</strong></td>
                <td>{{ $projectDuration ? $projectDuration . ' days' : 'Not calculated' }}</td>
            </tr>
            <tr>
                <td><strong>Created At</strong></td>
                <td>{{ $project->created_at->format('F j, Y \a\t g:i A') }}</td>
            </tr>
            <tr>
                <td><strong>Last Updated</strong></td>
                <td>{{ $project->updated_at->format('F j, Y \a\t g:i A') }}</td>
            </tr>
        </table>
    </div>

    <!-- Project Statistics Section -->
    <div class="section">
        <div class="section-title">📈 Project Statistics</div>

        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <div class="stats-value">{{ $projectStats['total_tasks'] }}</div>
                    <div class="stats-label">Total Tasks</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $projectStats['completed_tasks'] }}</div>
                    <div class="stats-label">Completed</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $projectStats['in_progress_tasks'] }}</div>
                    <div class="stats-label">In Progress</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $projectStats['completion_rate'] }}%</div>
                    <div class="stats-label">Completion Rate</div>
                </div>
            </div>
            <div class="stats-row">
                <div class="stats-cell">
                    <div class="stats-value">{{ $projectStats['pending_tasks'] }}</div>
                    <div class="stats-label">Pending</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $projectStats['overdue_tasks'] }}</div>
                    <div class="stats-label">Overdue</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ count($teamPerformance) }}</div>
                    <div class="stats-label">Team Members</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ count($folders) }}</div>
                    <div class="stats-label">Folders</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance Section -->
    @if(count($teamPerformance) > 0)
    <div class="section">
        <div class="section-title">👥 Team Performance</div>

        @foreach($teamPerformance as $member)
        <div class="team-member">
            <div class="member-name">{{ $member['user']->name }}</div>
            <div class="member-stats">
                <strong>Total Tasks:</strong> {{ $member['total_tasks'] }} |
                <strong>Completed:</strong> {{ $member['completed_tasks'] }} |
                <strong>In Progress:</strong> {{ $member['in_progress_tasks'] }} |
                <strong>Completion Rate:</strong> {{ $member['completion_rate'] }}%
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Project Folders Section -->
    @if(count($folders) > 0)
    <div class="section">
        <div class="section-title">📁 Project Structure</div>

        <table class="info-table">
            <tr>
                <th>Folder Name</th>
                <th>Subfolders</th>
                <th>Tasks</th>
                <th>Created</th>
            </tr>
            @foreach($folders as $folder)
            <tr>
                <td>{{ $folder->name }}</td>
                <td>{{ $folder->children_count ?? 0 }}</td>
                <td>{{ $folder->tasks_count ?? 0 }}</td>
                <td>{{ $folder->created_at->format('M j, Y') }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <!-- All Tasks Section -->
    <div class="section page-break">
        <div class="section-title">📋 Complete Tasks Details</div>

        @if($allTasks->count() > 0)
            @foreach($allTasks as $task)
            <div class="task-separator">
                <div class="task-number">Task {{ $loop->iteration }}</div>

                <!-- Task Name & Description Row -->
                <table class="task-table">
                    <tr>
                        <td colspan="8" style="background: #f8f9fa; font-weight: bold; padding: 10px;">
                            <strong>{{ $task->title }}</strong>
                            @if($task->description)
                                <br><small style="color: #666; font-weight: normal;">{{ $task->description }}</small>
                            @endif
                            <br><small style="color: #007bff; font-weight: normal;">
                                📁 {{ implode(' → ', $task->folder_path) }}
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <th style="width: 12%;">Status & Progress</th>
                        <th style="width: 10%;">Priority</th>
                        <th style="width: 15%;">Assignee</th>
                        <th style="width: 12%;">Created</th>
                        <th style="width: 12%;">Due Date</th>
                        <th style="width: 12%;">Completed</th>
                        <th style="width: 12%;">Duration</th>
                        <th style="width: 15%;">Time Left</th>
                    </tr>
                    <tr>
                        <td>
                            <span class="status-badge status-{{ $task->status }}">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </td>
                        <td>
                            <span class="priority-badge priority-{{ $task->priority ?? 'medium' }}">
                                {{ ucfirst($task->priority ?? 'Medium') }}
                            </span>
                        </td>
                        <td>
                            {{ $task->assignee->name ?? 'Unassigned' }}
                            @if($task->assignee)
                                <br><small style="color: #666;">{{ $task->assignee->email }}</small>
                            @endif
                        </td>
                        <td>{{ $task->created_at->format('M j, Y') }}</td>
                        <td>
                            @if($task->due_date)
                                {{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}
                                @if($task->is_overdue)
                                    <br><small style="color: #dc3545;">Overdue</small>
                                @endif
                            @else
                                No due date
                            @endif
                        </td>
                        <td>
                            @if($task->status === 'completed' && $task->updated_at)
                                {{ $task->updated_at->format('M j, Y') }}
                            @else
                                Not completed
                            @endif
                        </td>
                        <td>
                            @if($task->created_at && $task->due_date)
                                @php
                                    $duration = $task->created_at->diffInDays($task->due_date);
                                    $hours = $task->created_at->diffInHours($task->due_date) % 24;
                                @endphp
                                {{ round($duration) }}d {{ round($hours) }}h
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($task->due_date && $task->status !== 'completed')
                                @php
                                    $timeLeft = now()->diffInDays($task->due_date, false);
                                    $hoursLeft = now()->diffInHours($task->due_date) % 24;
                                @endphp
                                @if($timeLeft >= 0)
                                    {{ round($timeLeft) }}d {{ round($hoursLeft) }}h
                                @else
                                    <span style="color: #dc3545;">Overdue</span>
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Task Attachments -->
                @if($task->attachments && $task->attachments->count() > 0)
                <div style="margin-top: 10px;">
                    <strong>Attachments:</strong>
                    @foreach($task->attachments as $attachment)
                        <span style="background: #e9ecef; padding: 2px 6px; border-radius: 3px; margin-right: 5px; font-size: 8px;">
                            📎 {{ $attachment->original_name }}
                        </span>
                    @endforeach
                </div>
                @endif

                <!-- Task History -->
                @if(isset($allTaskHistory[$task->id]) && $allTaskHistory[$task->id]->count() > 0)
                <div style="margin-top: 15px;">
                    <strong style="color: #1c3644;">Task History:</strong>
                    @foreach($allTaskHistory[$task->id] as $history)
                    <div class="history-item">
                        <div class="history-title">{{ $history->title }}</div>
                        @if($history->description)
                            <div style="margin: 4px 0; font-size: 8px;">{{ $history->description }}</div>
                        @endif
                        <div class="history-meta">
                            <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $history->type)) }} |
                            <strong>By:</strong> {{ $history->user->name ?? 'System' }} |
                            <strong>Date:</strong> {{ $history->created_at->format('M j, Y g:i A') }}
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        @else
            <div style="text-align: center; padding: 40px; color: #666;">
                <strong>No tasks found for this project.</strong>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>Orion Contracting - Full Project Report | Generated on {{ $exportDate->format('F j, Y \a\t g:i A') }}</div>
        <div>Project: {{ $project->name }} ({{ $project->short_code }}) | Total Tasks: {{ $projectStats['total_tasks'] }} | Completion Rate: {{ $projectStats['completion_rate'] }}%</div>
    </div>
</body>
</html>
