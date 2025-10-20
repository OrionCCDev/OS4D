<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Users Evaluation Report - {{ $periodLabel }}</title>
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
        .summary-section {
            background-color: #f8f9fc;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e3e6f0;
        }
        .summary-section h2 {
            margin: 0 0 10px 0;
            color: #4e73df;
            font-size: 18px;
        }
        .summary-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .summary-stat {
            text-align: center;
            padding: 10px;
            background-color: white;
            border: 1px solid #e3e6f0;
            flex: 1;
            margin: 0 5px;
        }
        .summary-stat h3 {
            font-size: 24px;
            margin: 0;
            color: #4e73df;
        }
        .summary-stat p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 10px;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
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
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #e3e6f0;
            font-size: 10px;
        }
        .rank-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .rank-1 {
            background-color: #ffd700;
            color: #333;
        }
        .rank-2 {
            background-color: #c0c0c0;
            color: #333;
        }
        .rank-3 {
            background-color: #cd7f32;
            color: white;
        }
        .rank-other {
            background-color: #e3e6f0;
            color: #333;
        }
        .score-excellent {
            color: #1cc88a;
            font-weight: bold;
        }
        .score-good {
            color: #36b9cc;
            font-weight: bold;
        }
        .score-average {
            color: #f6c23e;
            font-weight: bold;
        }
        .score-poor {
            color: #e74a3b;
            font-weight: bold;
        }
        .user-detail-section {
            margin-top: 30px;
            page-break-before: always;
        }
        .user-card {
            background-color: #f8f9fc;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e3e6f0;
            page-break-inside: avoid;
        }
        .user-card h4 {
            margin: 0 0 10px 0;
            color: #4e73df;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        .metric-box {
            background-color: white;
            padding: 10px;
            border: 1px solid #e3e6f0;
            text-align: center;
        }
        .metric-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #4e73df;
        }
        .metric-box .label {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
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
        <h1>Employee Evaluation Report - All Users</h1>
        <p><strong>Evaluation Period:</strong> {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        <p><strong>Evaluation Type:</strong> {{ ucfirst($evaluationType) }}</p>
        <p><strong>Period:</strong> {{ $periodLabel }}</p>
        <p><strong>Generated:</strong> {{ $generatedAt->format('M d, Y H:i:s') }}</p>
        <p><strong>Generated By:</strong> {{ $evaluatedBy->name }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h2>Summary</h2>
        <p><strong>Total Employees Evaluated:</strong> {{ $totalEvaluations }}</p>
        <div class="summary-stats">
            <div class="summary-stat">
                <h3>{{ number_format(collect($evaluations)->avg('metrics.performance_score'), 1) }}</h3>
                <p>Average Performance Score</p>
            </div>
            <div class="summary-stat">
                <h3>{{ number_format(collect($evaluations)->avg('metrics.completion_rate'), 1) }}%</h3>
                <p>Average Completion Rate</p>
            </div>
            <div class="summary-stat">
                <h3>{{ number_format(collect($evaluations)->avg('metrics.on_time_rate'), 1) }}%</h3>
                <p>Average On-Time Rate</p>
            </div>
            <div class="summary-stat">
                <h3>{{ number_format(collect($evaluations)->sum('metrics.completed_tasks')) }}</h3>
                <p>Total Tasks Completed</p>
            </div>
        </div>
    </div>

    <!-- Performance Rankings Table -->
    <div class="section">
        <h3>Performance Rankings</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Rank</th>
                    <th style="width: 25%;">Employee Name</th>
                    <th style="width: 20%;">Email</th>
                    <th style="width: 12%;">Score</th>
                    <th style="width: 10%;">Completed</th>
                    <th style="width: 12%;">On-Time %</th>
                    <th style="width: 10%;">Overdue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evaluations as $evalData)
                    <tr>
                        <td>
                            <span class="rank-badge
                                @if($evalData['rank'] == 1) rank-1
                                @elseif($evalData['rank'] == 2) rank-2
                                @elseif($evalData['rank'] == 3) rank-3
                                @else rank-other
                                @endif">
                                #{{ $evalData['rank'] }}
                            </span>
                        </td>
                        <td>{{ $evalData['user']->name }}</td>
                        <td>{{ $evalData['user']->email }}</td>
                        <td>
                            <span class="
                                @if($evalData['metrics']['performance_score'] >= 80) score-excellent
                                @elseif($evalData['metrics']['performance_score'] >= 60) score-good
                                @elseif($evalData['metrics']['performance_score'] >= 40) score-average
                                @else score-poor
                                @endif">
                                {{ number_format($evalData['metrics']['performance_score'], 1) }}
                            </span>
                        </td>
                        <td>{{ $evalData['metrics']['completed_tasks'] }}</td>
                        <td>{{ number_format($evalData['metrics']['on_time_rate'], 1) }}%</td>
                        <td>{{ $evalData['metrics']['overdue_tasks'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Detailed User Evaluations -->
    <div class="user-detail-section">
        <h2 style="color: #4e73df; border-bottom: 3px solid #e3e6f0; padding-bottom: 10px;">Detailed Employee Evaluations</h2>

        @foreach($evaluations as $evalData)
            <div class="user-card">
                <h4>
                    <span>
                        <span class="rank-badge
                            @if($evalData['rank'] == 1) rank-1
                            @elseif($evalData['rank'] == 2) rank-2
                            @elseif($evalData['rank'] == 3) rank-3
                            @else rank-other
                            @endif">
                            #{{ $evalData['rank'] }}
                        </span>
                        {{ $evalData['user']->name }}
                    </span>
                    <span class="
                        @if($evalData['metrics']['performance_score'] >= 80) score-excellent
                        @elseif($evalData['metrics']['performance_score'] >= 60) score-good
                        @elseif($evalData['metrics']['performance_score'] >= 40) score-average
                        @else score-poor
                        @endif">
                        Score: {{ number_format($evalData['metrics']['performance_score'], 1) }}
                    </span>
                </h4>
                <p style="margin: 5px 0;"><strong>Email:</strong> {{ $evalData['user']->email }}</p>
                <p style="margin: 5px 0;"><strong>Role:</strong> {{ ucfirst($evalData['user']->role) }}</p>

                <div class="metrics-grid">
                    <div class="metric-box">
                        <div class="value">{{ $evalData['metrics']['total_tasks'] }}</div>
                        <div class="label">Total Tasks</div>
                    </div>
                    <div class="metric-box">
                        <div class="value">{{ $evalData['metrics']['completed_tasks'] }}</div>
                        <div class="label">Completed Tasks</div>
                    </div>
                    <div class="metric-box">
                        <div class="value">{{ number_format($evalData['metrics']['completion_rate'], 1) }}%</div>
                        <div class="label">Completion Rate</div>
                    </div>
                    <div class="metric-box">
                        <div class="value">{{ number_format($evalData['metrics']['on_time_rate'], 1) }}%</div>
                        <div class="label">On-Time Rate</div>
                    </div>
                </div>

                <div style="margin-top: 10px; padding: 10px; background-color: white; border: 1px solid #e3e6f0;">
                    <p style="margin: 3px 0; font-size: 10px;"><strong>Overdue Tasks:</strong> {{ $evalData['metrics']['overdue_tasks'] }}</p>
                    <p style="margin: 3px 0; font-size: 10px;"><strong>Evaluation Date:</strong> {{ $evalData['evaluation']->created_at->format('M d, Y') }}</p>
                    <p style="margin: 3px 0; font-size: 10px;"><strong>Status:</strong> {{ ucfirst($evalData['evaluation']->status) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated by OrionDesigners Evaluation System on {{ $generatedAt->format('F d, Y \a\t H:i:s') }}</p>
        <p>This report contains confidential employee performance information.</p>
    </div>
</body>
</html>

