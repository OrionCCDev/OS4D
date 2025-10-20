<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Performance Report - {{ $userReport['user']['name'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4e73df;
        }
        .header h1 {
            color: #4e73df;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .user-info {
            background-color: #f8f9fc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .user-info h2 {
            margin: 0 0 10px 0;
            color: #4e73df;
            font-size: 18px;
        }
        .user-info p {
            margin: 5px 0;
        }
        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .metrics-row {
            display: table-row;
        }
        .metric-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            margin: 5px;
            background-color: #f8f9fc;
            border-radius: 5px;
            text-align: center;
        }
        .metric-card h3 {
            font-size: 28px;
            margin: 0;
            color: #4e73df;
        }
        .metric-card p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 11px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h3 {
            color: #4e73df;
            border-bottom: 2px solid #e3e6f0;
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #4e73df;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #e3e6f0;
            font-size: 11px;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fc;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e3e6f0;
            border-radius: 3px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background-color: #4e73df;
            text-align: center;
            color: white;
            font-size: 10px;
            line-height: 20px;
        }
        .progress-fill.success {
            background-color: #1cc88a;
        }
        .progress-fill.warning {
            background-color: #f6c23e;
        }
        .progress-fill.danger {
            background-color: #e74a3b;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #1cc88a;
            color: white;
        }
        .badge-warning {
            background-color: #f6c23e;
            color: white;
        }
        .badge-danger {
            background-color: #e74a3b;
            color: white;
        }
        .badge-info {
            background-color: #36b9cc;
            color: white;
        }
        .badge-secondary {
            background-color: #858796;
            color: white;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e3e6f0;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .summary-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-grid td {
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>User Performance Report</h1>
        <p>{{ $userReport['user']['name'] }} - {{ $userReport['user']['email'] }}</p>
        <p>Generated on {{ now()->format('F d, Y') }}</p>
        @if(isset($filters['date_from']) || isset($filters['date_to']))
            <p style="font-size: 11px;">
                Report Period:
                {{ $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('M d, Y') : 'Start' }}
                to
                {{ $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('M d, Y') : 'Present' }}
            </p>
        @endif
    </div>

    <div class="user-info">
        <h2>{{ $userReport['user']['name'] }}</h2>
        <p><strong>Email:</strong> {{ $userReport['user']['email'] }}</p>
        @if(isset($userReport['user']['position']))
            <p><strong>Position:</strong> {{ $userReport['user']['position'] }}</p>
        @endif
    </div>

    <div class="section">
        <h3>Performance Summary</h3>
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="metric-card">
                        <h3>{{ $userReport['total_tasks'] }}</h3>
                        <p>Total Tasks</p>
                    </div>
                </td>
                <td>
                    <div class="metric-card">
                        <h3>{{ $userReport['completed_tasks'] }}</h3>
                        <p>Completed Tasks</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="metric-card">
                        <h3>{{ $userReport['completion_rate'] }}%</h3>
                        <p>Completion Rate</p>
                    </div>
                </td>
                <td>
                    <div class="metric-card">
                        <h3>{{ $userReport['performance_score'] }}%</h3>
                        <p>Performance Score</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Detailed Metrics</h3>
        <table>
            <tr>
                <th>Metric</th>
                <th>Value</th>
                <th>Performance</th>
            </tr>
            <tr>
                <td>On-Time Tasks</td>
                <td>{{ $userReport['on_time_tasks'] }} / {{ $userReport['completed_tasks'] }}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill {{ $userReport['on_time_rate'] >= 80 ? 'success' : ($userReport['on_time_rate'] >= 60 ? 'warning' : 'danger') }}"
                             style="width: {{ $userReport['on_time_rate'] }}%">
                            {{ $userReport['on_time_rate'] }}%
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Overdue Tasks</td>
                <td>{{ $userReport['overdue_tasks'] }}</td>
                <td>
                    @php
                        $overdueRate = $userReport['total_tasks'] > 0 ? round(($userReport['overdue_tasks'] / $userReport['total_tasks']) * 100, 1) : 0;
                    @endphp
                    <div class="progress-bar">
                        <div class="progress-fill {{ $overdueRate <= 10 ? 'success' : ($overdueRate <= 25 ? 'warning' : 'danger') }}"
                             style="width: {{ min($overdueRate, 100) }}%">
                            {{ $overdueRate }}%
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Completion Rate</td>
                <td>{{ $userReport['completed_tasks'] }} / {{ $userReport['total_tasks'] }}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill {{ $userReport['completion_rate'] >= 80 ? 'success' : ($userReport['completion_rate'] >= 60 ? 'warning' : 'danger') }}"
                             style="width: {{ $userReport['completion_rate'] }}%">
                            {{ $userReport['completion_rate'] }}%
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Average Completion Time</td>
                <td colspan="2">{{ $userReport['average_completion_time'] > 0 ? number_format($userReport['average_completion_time'], 1) : '0' }} days</td>
            </tr>
        </table>
    </div>

    @if(!empty($userReport['tasks_by_priority']) && $userReport['tasks_by_priority']->count() > 0)
    <div class="section">
        <h3>Tasks by Priority</h3>
        <table>
            <tr>
                <th>Priority</th>
                <th>Count</th>
                <th>Percentage</th>
            </tr>
            @foreach(['urgent' => 'Urgent', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $key => $label)
                @php
                    $count = $userReport['tasks_by_priority']->get($key, 0);
                    $percentage = $userReport['total_tasks'] > 0 ? round(($count / $userReport['total_tasks']) * 100, 1) : 0;
                @endphp
                @if($count > 0)
                <tr>
                    <td>
                        <span class="badge badge-{{ $key == 'urgent' ? 'danger' : ($key == 'high' ? 'warning' : ($key == 'medium' ? 'info' : 'secondary')) }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td>{{ $count }}</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $percentage }}%">
                                {{ $percentage }}%
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
            @endforeach
        </table>
    </div>
    @endif

    <div class="section">
        <h3>Performance Grade</h3>
        @php
            $grade = $userReport['performance_score'] >= 90 ? 'A+' :
                    ($userReport['performance_score'] >= 80 ? 'A' :
                    ($userReport['performance_score'] >= 70 ? 'B+' :
                    ($userReport['performance_score'] >= 60 ? 'B' :
                    ($userReport['performance_score'] >= 50 ? 'C' : 'D'))));
            $gradeColor = $userReport['performance_score'] >= 80 ? 'success' :
                         ($userReport['performance_score'] >= 60 ? 'warning' : 'danger');
        @endphp
        <p style="font-size: 14px;">
            Based on the performance metrics, <strong>{{ $userReport['user']['name'] }}</strong> has achieved a grade of:
        </p>
        <p style="text-align: center; margin: 20px 0;">
            <span style="font-size: 48px; color: {{ $gradeColor == 'success' ? '#1cc88a' : ($gradeColor == 'warning' ? '#f6c23e' : '#e74a3b') }}; font-weight: bold;">
                {{ $grade }}
            </span>
        </p>
        <p style="font-size: 12px; color: #666; text-align: center;">
            Performance Score: {{ $userReport['performance_score'] }}%
        </p>
    </div>

    <div class="footer">
        <p>This report was automatically generated by OrionDesigners Performance Management System</p>
        <p>© {{ now()->year }} OrionDesigners. All rights reserved.</p>
    </div>
</body>
</html>

