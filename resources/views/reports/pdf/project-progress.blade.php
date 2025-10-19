<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Progress Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4472C4;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4472C4;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #666;
            margin-top: 5px;
        }
        .project-section {
            page-break-inside: avoid;
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .project-header {
            background-color: #f8f9fa;
            padding: 10px;
            margin: -15px -15px 15px -15px;
            border-bottom: 2px solid #4472C4;
        }
        .project-title {
            font-size: 18px;
            font-weight: bold;
            color: #4472C4;
            margin: 0;
        }
        .project-info {
            color: #666;
            margin-top: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-active { background-color: #28c76f; color: white; }
        .status-completed { background-color: #4472C4; color: white; }
        .status-on_hold { background-color: #ff9f43; color: white; }
        .risk-badge {
            background-color: #ea5455;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 10px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-row {
            display: table-row;
        }
        .stat-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #4472C4;
        }
        .stat-label {
            color: #666;
            font-size: 11px;
            margin-top: 5px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #4472C4;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background-color: #4472C4;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        table td {
            padding: 8px 6px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
            vertical-align: top;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .task-row {
            min-height: 60px;
        }
        .task-row:hover {
            background-color: #e3f2fd;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background-color: #28c76f;
            text-align: center;
            color: white;
            font-weight: bold;
            line-height: 20px;
            font-size: 11px;
        }
        .metrics-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .metrics-table td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .metrics-label {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 40%;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Project Progress Report</h1>
        <div class="subtitle">
            Generated on {{ now()->format('F d, Y \a\t H:i') }}
        </div>
    </div>

    @if(count($projects) > 0)
        @foreach($projects as $index => $project)
            <div class="project-section {{ $index < count($projects) - 1 ? 'page-break' : '' }}">
            <div class="project-header">
                <h2 class="project-title">{{ $project['name'] }}</h2>
                <div class="project-info">
                    <strong>Code:</strong> {{ $project['short_code'] }} |
                    <strong>Owner:</strong> {{ $project['owner'] }} |
                    <span class="status-badge status-{{ $project['status'] }}">{{ ucfirst($project['status']) }}</span>
                    @if($project['is_at_risk'])
                        <span class="risk-badge">At Risk</span>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-row">
                    <div class="stat-cell">
                        <div class="stat-value">{{ $project['total_tasks'] }}</div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                    <div class="stat-cell">
                        <div class="stat-value" style="color: #28c76f;">{{ $project['completed_tasks'] }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-cell">
                        <div class="stat-value" style="color: #ff9f43;">{{ $project['in_progress_tasks'] }}</div>
                        <div class="stat-label">In Progress</div>
                    </div>
                    <div class="stat-cell">
                        <div class="stat-value" style="color: #ea5455;">{{ $project['overdue_tasks'] }}</div>
                        <div class="stat-label">Overdue</div>
                    </div>
                </div>
            </div>

            <!-- Overall Progress -->
            <div class="section-title">Overall Progress</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $project['completion_percentage'] }}%;">
                    {{ $project['completion_percentage'] }}% Complete
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="section-title">Key Metrics</div>
            <table class="metrics-table">
                <tr>
                    <td class="metrics-label">Completion Percentage</td>
                    <td>{{ $project['completion_percentage'] }}%</td>
                    <td class="metrics-label">On-Time Completion Rate</td>
                    <td>{{ $project['on_time_completion_rate'] }}%</td>
                </tr>
                <tr>
                    <td class="metrics-label">Team Size</td>
                    <td>{{ $project['team_size'] }} members</td>
                    <td class="metrics-label">Overdue Percentage</td>
                    <td style="color: {{ $project['overdue_percentage'] > 20 ? '#ea5455' : '#ff9f43' }};">
                        {{ $project['overdue_percentage'] }}%
                    </td>
                </tr>
                <tr>
                    <td class="metrics-label">Project Duration</td>
                    <td>{{ $project['project_duration_days'] }} days</td>
                    <td class="metrics-label">Days Remaining</td>
                    <td style="color: {{ $project['days_remaining'] !== null && $project['days_remaining'] < 0 ? '#ea5455' : '#28c76f' }};">
                        {{ $project['days_remaining'] ?? 'N/A' }} {{ $project['days_remaining'] !== null ? 'days' : '' }}
                    </td>
                </tr>
                <tr>
                    <td class="metrics-label">Created Date</td>
                    <td>{{ $project['created_at']->format('M d, Y') }}</td>
                    <td class="metrics-label">Due Date</td>
                    <td>{{ $project['due_date'] ? \Carbon\Carbon::parse($project['due_date'])->format('M d, Y') : 'Not Set' }}</td>
                </tr>
            </table>

            <!-- Task Status Breakdown -->
            <div class="section-title">Task Status Breakdown</div>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th style="text-align: center;">Count</th>
                        <th style="text-align: center;">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Completed</td>
                        <td style="text-align: center;">{{ $project['completed_tasks'] }}</td>
                        <td style="text-align: center;">{{ $project['completion_percentage'] }}%</td>
                    </tr>
                    <tr>
                        <td>In Progress</td>
                        <td style="text-align: center;">{{ $project['in_progress_tasks'] }}</td>
                        <td style="text-align: center;">{{ $project['total_tasks'] > 0 ? round(($project['in_progress_tasks'] / $project['total_tasks']) * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Pending</td>
                        <td style="text-align: center;">{{ $project['pending_tasks'] }}</td>
                        <td style="text-align: center;">{{ $project['total_tasks'] > 0 ? round(($project['pending_tasks'] / $project['total_tasks']) * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Overdue</td>
                        <td style="text-align: center;">{{ $project['overdue_tasks'] }}</td>
                        <td style="text-align: center;">{{ $project['overdue_percentage'] }}%</td>
                    </tr>
                </tbody>
            </table>

            <!-- Priority Distribution -->
            <div class="section-title">Priority Distribution</div>
            <table>
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th style="text-align: center;">Count</th>
                        <th style="text-align: center;">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>High Priority</td>
                        <td style="text-align: center;">{{ $project['high_priority_tasks'] }}</td>
                        <td style="text-align: center;">{{ $project['total_tasks'] > 0 ? round(($project['high_priority_tasks'] / $project['total_tasks']) * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Medium Priority</td>
                        <td style="text-align: center;">{{ $project['medium_priority_tasks'] }}</td>
                        <td style="text-align: center;">{{ $project['total_tasks'] > 0 ? round(($project['medium_priority_tasks'] / $project['total_tasks']) * 100, 1) : 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Low Priority</td>
                        <td style="text-align: center;">{{ $project['low_priority_tasks'] }}</td>
                        <td style="text-align: center;">{{ $project['total_tasks'] > 0 ? round(($project['low_priority_tasks'] / $project['total_tasks']) * 100, 1) : 0 }}%</td>
                    </tr>
                </tbody>
            </table>

            <!-- Team Performance -->
            @if(count($project['team_performance']) > 0)
                <div class="section-title">Team Member Performance</div>
                <table>
                    <thead>
                        <tr>
                            <th>Team Member</th>
                            <th style="text-align: center;">Total Tasks</th>
                            <th style="text-align: center;">Completed</th>
                            <th style="text-align: center;">Pending</th>
                            <th style="text-align: center;">Overdue</th>
                            <th style="text-align: center;">Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project['team_performance'] as $member)
                            <tr>
                                <td>{{ $member['user_name'] }}</td>
                                <td style="text-align: center;">{{ $member['total_tasks'] }}</td>
                                <td style="text-align: center;">{{ $member['completed_tasks'] }}</td>
                                <td style="text-align: center;">{{ $member['pending_tasks'] }}</td>
                                <td style="text-align: center;">{{ $member['overdue_tasks'] }}</td>
                                <td style="text-align: center;">{{ $member['completion_rate'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Detailed Tasks List -->
            @if(count($project['recent_tasks']) > 0)
                <div class="section-title">Complete Tasks Details</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 35%;">Task Name & Description</th>
                            <th style="width: 8%; text-align: center;">Status</th>
                            <th style="width: 7%; text-align: center;">Priority</th>
                            <th style="width: 10%;">Assignee</th>
                            <th style="width: 8%; text-align: center;">Created</th>
                            <th style="width: 8%; text-align: center;">Due Date</th>
                            <th style="width: 8%; text-align: center;">Completed</th>
                            <th style="width: 8%; text-align: center;">Duration</th>
                            <th style="width: 8%; text-align: center;">Time Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project['recent_tasks'] as $task)
                            <tr class="task-row" style="{{ $task['is_overdue'] ? 'background-color: #fff5f5;' : '' }}">
                                <td style="font-weight: bold; {{ $task['is_overdue'] ? 'color: #dc3545;' : '' }}; vertical-align: top; padding: 8px; min-height: 60px;">
                                    <div style="margin-bottom: 4px; font-size: 12px; font-weight: bold; color: #2c3e50;">{{ $task['name'] }}</div>
                                    @if($task['description'])
                                        <div style="font-size: 10px; color: #555; font-weight: normal; margin-top: 4px; line-height: 1.4; margin-bottom: 6px;">
                                            {{ Str::limit($task['description'], 120) }}
                                        </div>
                                    @endif
                                    @if($task['is_overdue'])
                                        <div style="color: #dc3545; font-weight: bold; font-size: 9px; margin-top: 4px; background-color: #f8d7da; padding: 3px 6px; border-radius: 4px; display: inline-block; border: 1px solid #f5c6cb;">⚠ OVERDUE</div>
                                    @endif
                                    @if($task['completion_notes'])
                                        <div style="font-size: 9px; color: #155724; font-style: italic; margin-top: 4px; background-color: #d4edda; padding: 4px 6px; border-radius: 4px; border: 1px solid #c3e6cb; line-height: 1.3;">
                                            <strong>Notes:</strong> {{ Str::limit($task['completion_notes'], 100) }}
                                        </div>
                                    @endif
                                    @if($task['assignee_email'])
                                        <div style="font-size: 8px; color: #6c757d; margin-top: 3px; font-style: italic;">
                                            Assigned to: {{ $task['assignee_email'] }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <span style="
                                        display: inline-block;
                                        padding: 2px 6px;
                                        border-radius: 3px;
                                        font-size: 9px;
                                        font-weight: bold;
                                        background-color: {{ $task['status'] === 'completed' ? '#28c76f' : ($task['status'] === 'in_progress' || $task['status'] === 'workingon' ? '#ff9f43' : ($task['status'] === 'assigned' ? '#17a2b8' : '#6c757d')) }};
                                        color: white;
                                    ">
                                        {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                    </span>
                                    <div style="margin-top: 4px;">
                                        <div style="background-color: #e9ecef; height: 4px; border-radius: 2px; width: 100%;">
                                            <div style="background-color: {{ $task['progress_stage'] === 'completed' ? '#28c76f' : ($task['progress_stage'] === 'client_review' ? '#ff9f43' : '#007bff') }}; height: 4px; border-radius: 2px; width: {{ $task['progress_percentage'] ?? 0 }}%;"></div>
                                        </div>
                                        <div style="font-size: 8px; color: #666; margin-top: 2px;">
                                            {{ $task['progress_percentage'] ?? 0 }}% - {{ $task['progress_status'] ?? 'Unknown' }}
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span style="
                                        display: inline-block;
                                        padding: 2px 6px;
                                        border-radius: 3px;
                                        font-size: 9px;
                                        font-weight: bold;
                                        background-color: {{ $task['priority'] === 'high' ? '#dc3545' : ($task['priority'] === 'medium' ? '#ffc107' : '#17a2b8') }};
                                        color: {{ $task['priority'] === 'medium' ? '#000' : 'white' }};
                                    ">
                                        {{ ucfirst($task['priority']) }}
                                    </span>
                                </td>
                                <td style="vertical-align: top; padding: 8px;">
                                    <div style="font-weight: bold; font-size: 10px; margin-bottom: 2px;">{{ $task['assignee'] }}</div>
                                    @if($task['assignee_email'])
                                        <div style="font-size: 8px; color: #666; margin-bottom: 2px;">{{ $task['assignee_email'] }}</div>
                                    @endif
                                    <div style="font-size: 8px; color: #999; background-color: #f8f9fa; padding: 2px 4px; border-radius: 3px; display: inline-block;">
                                        Created by: {{ $task['created_by'] }}
                                    </div>
                                </td>
                                <td style="text-align: center; font-size: 10px; vertical-align: top; padding: 8px;">
                                    @if($task['created_at'])
                                        <div style="font-weight: bold;">{{ \Carbon\Carbon::parse($task['created_at'])->format('M d, Y') }}</div>
                                        <div style="font-size: 8px; color: #666;">{{ \Carbon\Carbon::parse($task['created_at'])->format('H:i') }}</div>
                                    @else
                                        <span style="color: #6c757d;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center; {{ $task['is_overdue'] ? 'color: #dc3545; font-weight: bold;' : '' }}; vertical-align: top; padding: 8px;">
                                    @if($task['due_date'])
                                        <div style="font-size: 10px; font-weight: bold;">{{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}</div>
                                        <div style="font-size: 8px; color: #666;">{{ \Carbon\Carbon::parse($task['due_date'])->format('H:i') }}</div>
                                        @if($task['assigned_at'])
                                            <div style="font-size: 8px; color: #999; background-color: #e9ecef; padding: 1px 3px; border-radius: 2px; margin-top: 2px; display: inline-block;">
                                                Assigned: {{ \Carbon\Carbon::parse($task['assigned_at'])->format('M d') }}
                                            </div>
                                        @endif
                                    @else
                                        <span style="color: #6c757d; font-size: 9px;">No Due Date</span>
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: top; padding: 8px;">
                                    @if($task['completed_at'])
                                        <div style="color: #28c76f; font-weight: bold; font-size: 10px;">
                                            {{ \Carbon\Carbon::parse($task['completed_at'])->format('M d, Y') }}
                                        </div>
                                        <div style="color: #28c76f; font-size: 8px;">{{ \Carbon\Carbon::parse($task['completed_at'])->format('H:i') }}</div>
                                        @if($task['started_at'])
                                            <div style="font-size: 8px; color: #999; background-color: #d4edda; padding: 1px 3px; border-radius: 2px; margin-top: 2px; display: inline-block;">
                                                Started: {{ \Carbon\Carbon::parse($task['started_at'])->format('M d') }}
                                            </div>
                                        @endif
                                    @else
                                        <span style="color: #6c757d; font-size: 9px;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center; font-size: 9px; vertical-align: top; padding: 8px;">
                                    @if($task['assigned_at'] && $task['completed_at'])
                                        @php
                                            $assignedDate = \Carbon\Carbon::parse($task['assigned_at']);
                                            $completedDate = \Carbon\Carbon::parse($task['completed_at']);
                                            $totalHours = $assignedDate->diffInHours($completedDate);
                                            $days = floor($totalHours / 24);
                                            $hours = $totalHours % 24;
                                        @endphp
                                        <div style="color: #155724; font-weight: bold; font-size: 10px; background-color: #d4edda; padding: 4px 6px; border-radius: 4px; display: inline-block; border: 1px solid #c3e6cb;">
                                            @if($days > 0)
                                                {{ $days }}d
                                            @endif
                                            @if($hours > 0)
                                                {{ $hours }}h
                                            @endif
                                            @if($days == 0 && $hours == 0)
                                                < 1h
                                            @endif
                                        </div>
                                        <div style="font-size: 8px; color: #155724; margin-top: 2px; font-weight: 500;">Duration</div>
                                    @elseif($task['assigned_at'] && $task['status'] !== 'completed')
                                        @php
                                            $assignedDate = \Carbon\Carbon::parse($task['assigned_at']);
                                            $totalHours = $assignedDate->diffInHours(now());
                                            $days = floor($totalHours / 24);
                                            $hours = $totalHours % 24;
                                        @endphp
                                        <div style="color: #856404; font-weight: bold; font-size: 10px; background-color: #fff3cd; padding: 4px 6px; border-radius: 4px; display: inline-block; border: 1px solid #ffeaa7;">
                                            @if($days > 0)
                                                {{ $days }}d
                                            @endif
                                            @if($hours > 0)
                                                {{ $hours }}h
                                            @endif
                                            @if($days == 0 && $hours == 0)
                                                < 1h
                                            @endif
                                        </div>
                                        <div style="font-size: 8px; color: #856404; margin-top: 2px; font-weight: 500;">Working</div>
                                    @else
                                        <span style="color: #6c757d; font-size: 9px;">-</span>
                                    @endif
                                </td>
                                <td style="text-align: center; font-size: 9px; vertical-align: top; padding: 8px;">
                                    @if($task['due_date'])
                                        @if($task['status'] === 'completed')
                                            <div style="color: #155724; font-weight: bold; font-size: 10px; background-color: #d4edda; padding: 4px 6px; border-radius: 4px; display: inline-block; border: 1px solid #c3e6cb;">
                                                ✓ Completed
                                            </div>
                                            <div style="font-size: 8px; color: #155724; margin-top: 2px; font-weight: 500;">On Time</div>
                                        @elseif($task['is_overdue'])
                                            @php
                                                $overdueDays = $task['days_overdue'];
                                                $overdueHours = \Carbon\Carbon::parse($task['due_date'])->diffInHours(now()) % 24;
                                            @endphp
                                            <div style="color: #721c24; font-weight: bold; font-size: 10px; background-color: #f8d7da; padding: 4px 6px; border-radius: 4px; display: inline-block; border: 1px solid #f5c6cb;">
                                                {{ $overdueDays }}d
                                                @if($overdueHours > 0)
                                                    {{ $overdueHours }}h
                                                @endif
                                            </div>
                                            <div style="font-size: 8px; color: #721c24; margin-top: 2px; font-weight: 500;">Overdue</div>
                                        @elseif($task['days_remaining'] == 0)
                                            <div style="color: #856404; font-weight: bold; font-size: 10px; background-color: #fff3cd; padding: 4px 6px; border-radius: 4px; display: inline-block; border: 1px solid #ffeaa7;">
                                                Due Today
                                            </div>
                                            <div style="font-size: 8px; color: #856404; margin-top: 2px; font-weight: 500;">Urgent</div>
                                        @elseif($task['days_remaining'] <= 3)
                                            @php
                                                $remainingDays = floor($task['days_remaining']);
                                                $remainingHours = round(($task['days_remaining'] - $remainingDays) * 24);
                                            @endphp
                                            <div style="color: #856404; font-weight: bold; font-size: 10px; background-color: #fff3cd; padding: 4px 6px; border-radius: 4px; display: inline-block; border: 1px solid #ffeaa7;">
                                                {{ $remainingDays }}d
                                                @if($remainingHours > 0)
                                                    {{ $remainingHours }}h
                                                @endif
                                            </div>
                                            <div style="font-size: 8px; color: #856404; margin-top: 2px; font-weight: 500;">Urgent</div>
                                        @else
                                            @php
                                                $remainingDays = floor($task['days_remaining']);
                                                $remainingHours = round(($task['days_remaining'] - $remainingDays) * 24);
                                            @endphp
                                            <div style="color: #0c5460; font-weight: bold; font-size: 10px; background-color: #d1ecf1; padding: 4px 6px; border-radius: 4px; display: inline-block; border: 1px solid #bee5eb;">
                                                {{ $remainingDays }}d
                                                @if($remainingHours > 0)
                                                    {{ $remainingHours }}h
                                                @endif
                                            </div>
                                            <div style="font-size: 8px; color: #0c5460; margin-top: 2px; font-weight: 500;">Remaining</div>
                                        @endif
                                    @else
                                        <span style="color: #6c757d; font-size: 9px;">No Due Date</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Task Summary Statistics -->
                <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
                    <div style="display: table; width: 100%;">
                        <div style="display: table-row;">
                            <div style="display: table-cell; width: 25%; text-align: center; padding: 5px;">
                                <div style="font-size: 16px; font-weight: bold; color: #4472C4;">{{ count($project['recent_tasks']) }}</div>
                                <div style="font-size: 10px; color: #666;">Total Tasks</div>
                            </div>
                            <div style="display: table-cell; width: 25%; text-align: center; padding: 5px;">
                                <div style="font-size: 16px; font-weight: bold; color: #28c76f;">{{ $project['recent_tasks']->where('status', 'completed')->count() }}</div>
                                <div style="font-size: 10px; color: #666;">Completed</div>
                            </div>
                            <div style="display: table-cell; width: 25%; text-align: center; padding: 5px;">
                                <div style="font-size: 16px; font-weight: bold; color: #ff9f43;">{{ $project['recent_tasks']->whereIn('status', ['in_progress', 'workingon'])->count() }}</div>
                                <div style="font-size: 10px; color: #666;">In Progress</div>
                            </div>
                            <div style="display: table-cell; width: 25%; text-align: center; padding: 5px;">
                                <div style="font-size: 16px; font-weight: bold; color: #dc3545;">{{ $project['recent_tasks']->where('is_overdue', true)->count() }}</div>
                                <div style="font-size: 10px; color: #666;">Overdue</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Priority & Status Breakdown -->
                <div style="margin-top: 15px;">
                    <div style="display: table; width: 100%;">
                        <div style="display: table-row;">
                            <div style="display: table-cell; width: 50%; padding-right: 10px;">
                                <div style="font-size: 12px; font-weight: bold; color: #4472C4; margin-bottom: 8px;">Priority Distribution</div>
                                <table style="width: 100%; font-size: 10px;">
                                    <tr>
                                        <td style="padding: 3px; background-color: #dc3545; color: white; font-weight: bold;">High Priority</td>
                                        <td style="padding: 3px; text-align: center; background-color: #f8f9fa;">{{ $project['recent_tasks']->where('priority', 'high')->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px; background-color: #ffc107; color: black; font-weight: bold;">Medium Priority</td>
                                        <td style="padding: 3px; text-align: center; background-color: #f8f9fa;">{{ $project['recent_tasks']->where('priority', 'medium')->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px; background-color: #17a2b8; color: white; font-weight: bold;">Low Priority</td>
                                        <td style="padding: 3px; text-align: center; background-color: #f8f9fa;">{{ $project['recent_tasks']->where('priority', 'low')->count() }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div style="display: table-cell; width: 50%; padding-left: 10px;">
                                <div style="font-size: 12px; font-weight: bold; color: #4472C4; margin-bottom: 8px;">Status Distribution</div>
                                <table style="width: 100%; font-size: 10px;">
                                    <tr>
                                        <td style="padding: 3px; background-color: #28c76f; color: white; font-weight: bold;">Completed</td>
                                        <td style="padding: 3px; text-align: center; background-color: #f8f9fa;">{{ $project['recent_tasks']->where('status', 'completed')->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px; background-color: #ff9f43; color: white; font-weight: bold;">In Progress</td>
                                        <td style="padding: 3px; text-align: center; background-color: #f8f9fa;">{{ $project['recent_tasks']->whereIn('status', ['in_progress', 'workingon'])->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px; background-color: #17a2b8; color: white; font-weight: bold;">Assigned</td>
                                        <td style="padding: 3px; text-align: center; background-color: #f8f9fa;">{{ $project['recent_tasks']->where('status', 'assigned')->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 3px; background-color: #6c757d; color: white; font-weight: bold;">Pending</td>
                                        <td style="padding: 3px; text-align: center; background-color: #f8f9fa;">{{ $project['recent_tasks']->where('status', 'pending')->count() }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Timeline Analysis -->
                @php
                    $completedTasks = $project['recent_tasks']->where('status', 'completed');
                    $avgCompletionTime = $completedTasks->where('assigned_at', '!=', null)->map(function($task) {
                        if ($task['assigned_at'] && $task['completed_at']) {
                            return \Carbon\Carbon::parse($task['assigned_at'])->diffInDays(\Carbon\Carbon::parse($task['completed_at']));
                        }
                        return null;
                    })->filter()->avg();
                @endphp

                @if($avgCompletionTime)
                <div style="margin-top: 15px; padding: 10px; background-color: #e8f4fd; border-radius: 5px; border-left: 4px solid #17a2b8;">
                    <div style="font-size: 12px; font-weight: bold; color: #4472C4; margin-bottom: 5px;">📊 Task Performance Insights</div>
                    <div style="font-size: 10px; color: #666;">
                        • Average completion time: <strong>{{ round($avgCompletionTime, 1) }} days</strong><br>
                        • Most productive period: {{ $completedTasks->count() > 0 ? 'Last ' . $completedTasks->count() . ' completed tasks' : 'No completed tasks yet' }}<br>
                        • Overdue tasks requiring attention: <strong style="color: #dc3545;">{{ $project['recent_tasks']->where('is_overdue', true)->count() }}</strong>
                    </div>
                </div>
                @endif
            @else
                <div class="section-title">Detailed Tasks List</div>
                <div style="text-align: center; padding: 20px; color: #6c757d; background-color: #f8f9fa; border-radius: 5px;">
                    <div style="font-size: 24px; margin-bottom: 10px;">📋</div>
                    <div style="font-weight: bold;">No tasks found for this project</div>
                    <div style="font-size: 11px; margin-top: 5px;">Tasks will appear here once they are created and assigned to this project.</div>
                </div>
            @endif

            <!-- Risk Assessment -->
            @if($project['is_at_risk'])
                <div class="section-title">Risk Assessment</div>
                <table>
                    <tr>
                        <td colspan="2" style="background-color: #fff3cd; color: #856404; font-weight: bold;">
                            ⚠ Action Required - This project needs attention
                        </td>
                    </tr>
                    @if($project['overdue_tasks'] > 0)
                        <tr>
                            <td style="width: 50px;">⚠</td>
                            <td>{{ $project['overdue_tasks'] }} overdue task(s) need immediate attention</td>
                        </tr>
                    @endif
                    @if($project['days_remaining'] !== null && $project['days_remaining'] < 7)
                        <tr>
                            <td style="width: 50px;">⚠</td>
                            <td>Project deadline is approaching ({{ $project['days_remaining'] }} days remaining)</td>
                        </tr>
                    @endif
                </table>
            @endif
        </div>
        @endforeach
    @else
        <div class="project-section">
            <div class="project-header">
                <h2 class="project-title">No Projects Found</h2>
                <div class="project-info">
                    No projects match the current filter criteria.
                </div>
            </div>
            <div style="text-align: center; padding: 40px; color: #6c757d;">
                <div style="font-size: 48px; margin-bottom: 20px;">📊</div>
                <h3>No Data Available</h3>
                <p>There are no projects to display in this report.</p>
                <p style="font-size: 12px; margin-top: 20px;">
                    Please check your filter settings or contact your administrator.
                </p>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by OrionDesigners Project Management System</p>
        <p>For internal use only | {{ now()->format('Y') }} © OrionDesigners</p>
    </div>
</body>
</html>

