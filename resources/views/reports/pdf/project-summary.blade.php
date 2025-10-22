<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Summary Report - {{ $project->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #696cff;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #696cff;
            margin: 0;
            font-size: 24px;
        }

        .header h2 {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: normal;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #f8f9fa;
            color: #495057;
            padding: 10px 15px;
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: bold;
            border-left: 4px solid #696cff;
        }

        .project-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .project-info-row {
            display: table-row;
        }

        .project-info-label {
            display: table-cell;
            width: 30%;
            padding: 8px 0;
            font-weight: bold;
            vertical-align: top;
        }

        .project-info-value {
            display: table-cell;
            width: 70%;
            padding: 8px 0;
            vertical-align: top;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stats-row {
            display: table-row;
        }

        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px 10px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #696cff;
            display: block;
        }

        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        .team-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .team-table th,
        .team-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }

        .team-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }

        .team-table td {
            font-size: 10px;
        }

        .folder-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .folder-title {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 8px 12px;
            margin: 0 0 10px 0;
            font-size: 13px;
            font-weight: bold;
            border-left: 4px solid #1976d2;
        }

        .sub-folder-title {
            background-color: #f5f5f5;
            color: #666;
            padding: 6px 10px;
            margin: 10px 0 8px 0;
            font-size: 12px;
            font-weight: bold;
            border-left: 3px solid #999;
        }

        .tasks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .tasks-table th,
        .tasks-table td {
            border: 1px solid #dee2e6;
            padding: 6px;
            text-align: left;
            font-size: 9px;
        }

        .tasks-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-in-progress {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-pending {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .priority-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .priority-high {
            background-color: #f8d7da;
            color: #721c24;
        }

        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }

        .priority-low {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .summary-section {
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            margin-top: 20px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-row {
            display: table-row;
        }

        .summary-col {
            display: table-cell;
            width: 50%;
            padding: 5px 10px;
            vertical-align: top;
        }

        .summary-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .summary-list li {
            padding: 3px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-list li:last-child {
            border-bottom: none;
        }

        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Project Summary Report</h1>
        <h2>{{ $project->name }} - {{ $project->short_code ?? 'N/A' }}</h2>
        <p>Generated on {{ $exportDate->format('M d, Y H:i') }}</p>
    </div>

    <!-- Project Overview -->
    <div class="section">
        <div class="section-title">Project Overview</div>
        <div class="project-info">
            <div class="project-info-row">
                <div class="project-info-label">Project Name:</div>
                <div class="project-info-value">{{ $project->name }}</div>
            </div>
            <div class="project-info-row">
                <div class="project-info-label">Project Code:</div>
                <div class="project-info-value">{{ $project->short_code ?? 'N/A' }}</div>
            </div>
            <div class="project-info-row">
                <div class="project-info-label">Status:</div>
                <div class="project-info-value">{{ ucfirst($project->status) }}</div>
            </div>
            <div class="project-info-row">
                <div class="project-info-label">Owner:</div>
                <div class="project-info-value">{{ $project->owner->name ?? 'N/A' }}</div>
            </div>
            <div class="project-info-row">
                <div class="project-info-label">Created Date:</div>
                <div class="project-info-value">{{ $projectTimeline['created_at']->format('M d, Y') }}</div>
            </div>
            <div class="project-info-row">
                <div class="project-info-label">Due Date:</div>
                <div class="project-info-value">
                    @if($projectTimeline['due_date'])
                        {{ \Carbon\Carbon::parse($projectTimeline['due_date'])->format('M d, Y') }}
                    @else
                        No due date
                    @endif
                </div>
            </div>
            <div class="project-info-row">
                <div class="project-info-label">Project Duration:</div>
                <div class="project-info-value">
                    @if($managerPlannedDuration)
                        {{ $managerPlannedDuration }} days
                    @else
                        Not specified
                    @endif
                </div>
            </div>
            <div class="project-info-row">
                <div class="project-info-label">Completion Rate:</div>
                <div class="project-info-value">{{ $projectStats['completion_rate'] }}%</div>
            </div>
        </div>
    </div>

    <!-- Project Statistics -->
    <div class="section">
        <div class="section-title">Project Statistics</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-number">{{ $projectStats['total_tasks'] }}</span>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $projectStats['completed_tasks'] }}</span>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $projectStats['in_progress_tasks'] }}</span>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $projectStats['overdue_tasks'] }}</span>
                    <div class="stat-label">Overdue</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Performance -->
    <div class="section">
        <div class="section-title">Team Members Performance</div>
        @if(count($teamPerformance) > 0)
            <table class="team-table">
                <thead>
                    <tr>
                        <th>Team Member</th>
                        <th>Total Tasks</th>
                        <th>Completed</th>
                        <th>In Progress</th>
                        <th>Completion Rate</th>
                        <th>Avg. Completion Time</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teamPerformance as $member)
                        <tr>
                            <td>
                                <strong>{{ $member['user']->name }}</strong><br>
                                <small>{{ $member['user']->email }}</small>
                            </td>
                            <td>{{ $member['total_tasks'] }}</td>
                            <td>{{ $member['completed_tasks'] }}</td>
                            <td>{{ $member['in_progress_tasks'] }}</td>
                            <td>{{ $member['completion_rate'] }}%</td>
                            <td>
                                @if($member['avg_completion_time'] > 0)
                                    {{ $member['avg_completion_time'] }} days
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($member['last_activity'])
                                    {{ $member['last_activity']->format('M d, Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No team members have been assigned to tasks in this project.</div>
        @endif
    </div>

    <!-- Project Structure & Tasks -->
    <div class="section">
        <div class="section-title">Project Structure & Tasks</div>
        @if(count($folderStructure) > 0)
            @foreach($folderStructure as $folderData)
                <div class="folder-section">
                    <div class="folder-title">{{ $folderData['folder']->name }}</div>

                    @if(count($folderData['tasks']) > 0)
                        <table class="tasks-table">
                            <thead>
                                <tr>
                                    <th>Task Title</th>
                                    <th>Assignee</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Created</th>
                                    <th>Due Date</th>
                                    <th>Completed</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($folderData['tasks'] as $task)
                                    <tr>
                                        <td>
                                            <div style="font-weight: bold;">{{ $task['title'] }}</div>
                                            <div style="font-size: 10px; color: #666; margin-top: 2px;">
                                                📁 {{ implode(' → ', $task['folder_path']) }}
                                            </div>
                                        </td>
                                        <td>{{ $task['assignee'] }}</td>
                                        <td>
                                            <span class="status-badge status-{{ $task['status'] === 'completed' ? 'completed' : ($task['status'] === 'in_progress' ? 'in-progress' : 'pending') }}">
                                                {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="priority-badge priority-{{ $task['priority'] }}">
                                                {{ ucfirst($task['priority']) }}
                                            </span>
                                        </td>
                                        <td>{{ $task['created_at']->format('M d, Y') }}</td>
                                        <td>
                                            @if($task['due_date'])
                                                {{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}
                                            @else
                                                No due date
                                            @endif
                                        </td>
                                        <td>
                                            @if($task['completed_at'])
                                                {{ \Carbon\Carbon::parse($task['completed_at'])->format('M d, Y') }}
                                            @else
                                                Not completed
                                            @endif
                                        </td>
                                        <td>
                                            @if($task['duration_days'])
                                                {{ $task['duration_days'] }} days
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-data">No tasks in this folder.</div>
                    @endif

                    <!-- Sub-folders -->
                    @if(count($folderData['sub_folders']) > 0)
                        @foreach($folderData['sub_folders'] as $subFolderData)
                            <div class="sub-folder-title">{{ $subFolderData['folder']->name }}</div>

                            @if(count($subFolderData['tasks']) > 0)
                                <table class="tasks-table">
                                    <thead>
                                        <tr>
                                            <th>Task Title</th>
                                            <th>Assignee</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Created</th>
                                            <th>Due Date</th>
                                            <th>Completed</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subFolderData['tasks'] as $task)
                                            <tr>
                                                <td>
                                                    <div style="font-weight: bold;">{{ $task['title'] }}</div>
                                                    <div style="font-size: 10px; color: #666; margin-top: 2px;">
                                                        📁 {{ implode(' → ', $task['folder_path']) }}
                                                    </div>
                                                </td>
                                                <td>{{ $task['assignee'] }}</td>
                                                <td>
                                                    <span class="status-badge status-{{ $task['status'] === 'completed' ? 'completed' : ($task['status'] === 'in_progress' ? 'in-progress' : 'pending') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="priority-badge priority-{{ $task['priority'] }}">
                                                        {{ ucfirst($task['priority']) }}
                                                    </span>
                                                </td>
                                                <td>{{ $task['created_at']->format('M d, Y') }}</td>
                                                <td>
                                                    @if($task['due_date'])
                                                        {{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}
                                                    @else
                                                        No due date
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task['completed_at'])
                                                        {{ \Carbon\Carbon::parse($task['completed_at'])->format('M d, Y') }}
                                                    @else
                                                        Not completed
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task['duration_days'])
                                                        {{ $task['duration_days'] }} days
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="no-data">No tasks in this sub-folder.</div>
                            @endif
                        @endforeach
                    @endif
                </div>
            @endforeach
        @else
            <div class="no-data">This project doesn't have any folders yet.</div>
        @endif
    </div>

    <!-- Project Summary -->
    <div class="summary-section">
        <div class="section-title">Project Summary</div>
        <div class="summary-grid">
            <div class="summary-col">
                <h6>Project Timeline</h6>
                <ul class="summary-list">
                    <li><strong>Created:</strong> {{ $projectTimeline['created_at']->format('M d, Y H:i') }}</li>
                    @if($projectTimeline['start_date'])
                        <li><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($projectTimeline['start_date'])->format('M d, Y') }}</li>
                    @endif
                    @if($projectTimeline['due_date'])
                        <li><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($projectTimeline['due_date'])->format('M d, Y') }}</li>
                    @endif
                    @if($projectTimeline['end_date'])
                        <li><strong>End Date:</strong> {{ \Carbon\Carbon::parse($projectTimeline['end_date'])->format('M d, Y') }}</li>
                    @endif
                    <li><strong>Last Updated:</strong> {{ $projectTimeline['updated_at']->format('M d, Y H:i') }}</li>
                </ul>
            </div>
            <div class="summary-col">
                <h6>Key Metrics</h6>
                <ul class="summary-list">
                    <li><strong>Total Tasks:</strong> {{ $projectStats['total_tasks'] }}</li>
                    <li><strong>Completed Tasks:</strong> {{ $projectStats['completed_tasks'] }}</li>
                    <li><strong>Completion Rate:</strong> {{ $projectStats['completion_rate'] }}%</li>
                    <li><strong>Overdue Tasks:</strong> {{ $projectStats['overdue_tasks'] }}</li>
                    @if($managerPlannedDuration)
                        <li><strong>Planned Duration:</strong> {{ $managerPlannedDuration }} days</li>
                    @endif
                    <li><strong>Team Members:</strong> {{ count($teamPerformance) }}</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
