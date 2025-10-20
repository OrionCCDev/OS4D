<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Performance Report - {{ $monthYear }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 25px;
            color: #2c3e50;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: linear-gradient(135deg, #f8f9fc 0%, #e3e6f0 100%);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #4e73df;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 32px;
            color: #4e73df;
            font-weight: 700;
        }
        .summary-card p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #4e73df;
            border-bottom: 2px solid #e3e6f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 22px;
        }
        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .performance-item {
            background-color: #f8f9fc;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e3e6f0;
        }
        .performance-item h4 {
            margin: 0 0 8px 0;
            color: #495057;
            font-size: 14px;
            font-weight: 600;
        }
        .performance-item .value {
            font-size: 24px;
            font-weight: 700;
            color: #4e73df;
        }
        .performance-item .value.excellent { color: #1cc88a; }
        .performance-item .value.good { color: #36b9cc; }
        .performance-item .value.average { color: #f6c23e; }
        .performance-item .value.poor { color: #e74a3b; }
        .project-list {
            background-color: #f8f9fc;
            border-radius: 8px;
            padding: 20px;
        }
        .project-item {
            background-color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            border-left: 4px solid #4e73df;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .project-item:last-child {
            margin-bottom: 0;
        }
        .project-item h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 16px;
        }
        .project-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .project-stat {
            text-align: center;
            padding: 8px;
            background-color: #f8f9fc;
            border-radius: 4px;
        }
        .project-stat .number {
            font-size: 18px;
            font-weight: 700;
            color: #4e73df;
        }
        .project-stat .label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 2px;
        }
        .tasks-list {
            margin-top: 15px;
        }
        .task-item {
            padding: 8px 12px;
            background-color: #f8f9fc;
            margin-bottom: 5px;
            border-radius: 4px;
            font-size: 14px;
            border-left: 3px solid #dee2e6;
        }
        .task-item.completed { border-left-color: #1cc88a; }
        .task-item.in-progress { border-left-color: #36b9cc; }
        .task-item.overdue { border-left-color: #e74a3b; }
        .task-item.pending { border-left-color: #f6c23e; }
        .footer {
            background-color: #f8f9fc;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .highlight {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .highlight h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .highlight p {
            margin: 0;
            color: #856404;
        }
        @media (max-width: 600px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
            .performance-grid {
                grid-template-columns: 1fr;
            }
            .project-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Monthly Performance Report</h1>
            <p>{{ $monthYear }} | {{ $user->name }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="greeting">
                <p>Hello <strong>{{ $user->name }}</strong>,</p>
                <p>Here's your comprehensive performance report for <strong>{{ $monthYear }}</strong>. This report includes your task completion statistics, project contributions, and overall performance metrics.</p>
            </div>

            <!-- Summary Cards -->
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

            <!-- Performance Highlights -->
            <div class="section">
                <h2>🎯 Performance Highlights</h2>
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
                <h2>📁 Project Contributions</h2>
                <div class="project-list">
                    @if(count($projectTasksData['projects']) > 0)
                        @foreach($projectTasksData['projects'] as $projectData)
                            <div class="project-item">
                                <h4>{{ $projectData['project']->name }} ({{ $projectData['project']->short_code }})</h4>
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
                                    <div class="tasks-list">
                                        <h5 style="margin: 15px 0 10px 0; color: #495057; font-size: 14px;">Recent Tasks:</h5>
                                        @foreach($projectData['tasks'] as $task)
                                            <div class="task-item {{ $task->status }}">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</strong> - {{ $task->title }}
                                                @if($task->due_date)
                                                    <span style="color: #6c757d; font-size: 12px;">
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
            </div>

            <!-- Performance Insights -->
            <div class="section">
                <h2>💡 Performance Insights</h2>
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

            <!-- Call to Action -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/reports/users/{{ $user->id }}" class="cta-button">
                    View Detailed Report
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>OrionDesigners Performance Management System</strong></p>
            <p>This report was generated on {{ $generatedAt->format('F d, Y \a\t H:i:s') }}</p>
            <p>For questions about this report, please contact engineering@orion-contracting.com</p>
            <p style="margin-top: 15px; font-size: 12px; color: #adb5bd;">
                This is an automated report. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
