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
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
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

    <div class="footer">
        <p>This report was generated by OrionDesigners Project Management System</p>
        <p>For internal use only | {{ now()->format('Y') }} © OrionDesigners</p>
    </div>
</body>
</html>

