<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Performance Report - {{ $user->name }} - {{ $monthYear }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
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
        .summary-section {
            background-color: #f8f9fc;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        .summary-card {
            background-color: white;
            padding: 15px;
            text-align: center;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
        }
        .summary-card h3 {
            font-size: 24px;
            margin: 0;
            color: #4e73df;
        }
        .summary-card p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 11px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            color: #4e73df;
            border-bottom: 2px solid #e3e6f0;
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .performance-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .performance-item {
            background-color: #f8f9fc;
            padding: 15px;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
        }
        .performance-item h4 {
            margin: 0 0 8px 0;
            color: #495057;
            font-size: 12px;
        }
        .performance-item .value {
            font-size: 20px;
            font-weight: bold;
            color: #4e73df;
        }
        .performance-item .value.excellent { color: #1cc88a; }
        .performance-item .value.good { color: #36b9cc; }
        .performance-item .value.average { color: #f6c23e; }
        .performance-item .value.poor { color: #e74a3b; }
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
        .project-section {
            background-color: #f8f9fc;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
        }
        .project-section h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 14px;
        }
        .project-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 10px 0;
        }
        .project-stat {
            text-align: center;
            padding: 8px;
            background-color: white;
            border: 1px solid #e3e6f0;
            border-radius: 3px;
        }
        .project-stat .number {
            font-size: 16px;
            font-weight: bold;
            color: #4e73df;
        }
        .project-stat .label {
            font-size: 10px;
            color: #6c757d;
            margin-top: 2px;
        }
        .task-list {
            margin-top: 10px;
        }
        .task-item {
            padding: 5px 8px;
            background-color: white;
            margin-bottom: 3px;
            border-radius: 3px;
            font-size: 10px;
            border-left: 3px solid #dee2e6;
        }
        .task-item.completed { border-left-color: #1cc88a; }
        .task-item.in-progress { border-left-color: #36b9cc; }
        .task-item.overdue { border-left-color: #e74a3b; }
        .task-item.pending { border-left-color: #f6c23e; }
        .highlight {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .highlight h4 {
            margin: 0 0 8px 0;
            color: #856404;
            font-size: 13px;
        }
        .highlight p {
            margin: 0;
            color: #856404;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e3e6f0;
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
    <!-- Header -->
    <div class="header">
        <h1>Monthly Performance Report</h1>
        <p><strong>Employee:</strong> {{ $user->name }} ({{ $user->email }})</p>
        <p><strong>Period:</strong> {{ $monthYear }}</p>
        <p><strong>Generated:</strong> {{ $generatedAt->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h2 style="margin-top: 0; color: #4e73df;">Performance Summary</h2>
        <div class="summary-cards">
            <div class="summary-card">
                <h3>{{ $performanceMetrics['performance_score'] }}</h3>
                <p>Performance Score</p>
            </div>
            <div class="summary-card">
                <h3>{{ $projectTasksData['total_tasks'] }}</h3>
                <p>Total Tasks</p>
            </div>
            <div class="summary-card">
                <h3>{{ $projectTasksData['completed_tasks'] }}</h3>
                <p>Completed Tasks</p>
            </div>
            <div class="summary-card">
                <h3>{{ $projectTasksData['completion_rate'] }}%</h3>
                <p>Completion Rate</p>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="section">
        <h2>Performance Metrics</h2>
        <div class="performance-grid">
            <div class="performance-item">
                <h4>On-Time Completion Rate</h4>
                <div class="value {{ $performanceMetrics['on_time_rate'] >= 80 ? 'excellent' : ($performanceMetrics['on_time_rate'] >= 60 ? 'good' : ($performanceMetrics['on_time_rate'] >= 40 ? 'average' : 'poor')) }}">
                    {{ $performanceMetrics['on_time_rate'] }}%
                </div>
            </div>
            <div class="performance-item">
                <h4>Overdue Tasks</h4>
                <div class="value {{ $performanceMetrics['overdue_tasks'] == 0 ? 'excellent' : ($performanceMetrics['overdue_tasks'] <= 2 ? 'good' : ($performanceMetrics['overdue_tasks'] <= 5 ? 'average' : 'poor')) }}">
                    {{ $performanceMetrics['overdue_tasks'] }}
                </div>
            </div>
            <div class="performance-item">
                <h4>Tasks in Progress</h4>
                <div class="value">{{ $projectTasksData['in_progress_tasks'] }}</div>
            </div>
            <div class="performance-item">
                <h4>Total Projects</h4>
                <div class="value">{{ count($projectTasksData['projects']) }}</div>
            </div>
        </div>
    </div>

    <!-- Project Contributions -->
    <div class="section">
        <h2>Project Contributions</h2>
        @if(count($projectTasksData['projects']) > 0)
            @foreach($projectTasksData['projects'] as $projectData)
                <div class="project-section">
                    <h3>{{ $projectData['project']->name }} ({{ $projectData['project']->short_code }})</h3>
                    <div class="project-stats">
                        <div class="project-stat">
                            <div class="number">{{ $projectData['total_tasks'] }}</div>
                            <div class="label">Total Tasks</div>
                        </div>
                        <div class="project-stat">
                            <div class="number">{{ $projectData['completed_tasks'] }}</div>
                            <div class="label">Completed</div>
                        </div>
                        <div class="project-stat">
                            <div class="number">{{ $projectData['completion_rate'] }}%</div>
                            <div class="label">Completion Rate</div>
                        </div>
                        <div class="project-stat">
                            <div class="number">{{ $projectData['overdue_tasks'] }}</div>
                            <div class="label">Overdue</div>
                        </div>
                    </div>

                    @if(count($projectData['tasks']) > 0)
                        <div class="task-list">
                            <h4 style="margin: 10px 0 5px 0; color: #495057; font-size: 12px;">Recent Tasks:</h4>
                            @foreach($projectData['tasks'] as $task)
                                <div class="task-item {{ $task->status }}">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</strong> - {{ $task->title }}
                                    @if($task->due_date)
                                        <span style="color: #6c757d; font-size: 9px;">
                                            (Due: {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }})
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <p style="text-align: center; color: #6c757d; font-style: italic;">
                No project tasks found for {{ $monthYear }}.
            </p>
        @endif
    </div>

    <!-- Performance Insights -->
    <div class="section">
        <h2>Performance Insights</h2>
        @if($performanceMetrics['performance_score'] >= 80)
            <div class="highlight">
                <h4>🌟 Excellent Performance!</h4>
                <p>You've achieved an outstanding performance score of {{ $performanceMetrics['performance_score'] }}. Keep up the excellent work!</p>
            </div>
        @elseif($performanceMetrics['performance_score'] >= 60)
            <div class="highlight">
                <h4>👍 Good Performance</h4>
                <p>You're performing well with a score of {{ $performanceMetrics['performance_score'] }}. There's room for improvement to reach the next level.</p>
            </div>
        @elseif($performanceMetrics['performance_score'] >= 40)
            <div class="highlight">
                <h4>📈 Room for Improvement</h4>
                <p>Your current score is {{ $performanceMetrics['performance_score'] }}. Focus on completing tasks on time and reducing overdue items.</p>
            </div>
        @else
            <div class="highlight">
                <h4>🎯 Focus Areas</h4>
                <p>Your performance score is {{ $performanceMetrics['performance_score'] }}. Consider prioritizing task completion and meeting deadlines.</p>
            </div>
        @endif

        @if($performanceMetrics['overdue_tasks'] > 0)
            <div class="highlight" style="background-color: #f8d7da; border-color: #f5c6cb;">
                <h4>⚠️ Overdue Tasks Alert</h4>
                <p>You have {{ $performanceMetrics['overdue_tasks'] }} overdue task(s). Please prioritize completing these tasks to improve your performance score.</p>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>OrionDesigners Performance Management System</strong></p>
        <p>This report was generated on {{ $generatedAt->format('F d, Y \a\t H:i:s') }}</p>
        <p>For questions about this report, please contact engineering@orion-contracting.com</p>
    </div>
</body>
</html>
