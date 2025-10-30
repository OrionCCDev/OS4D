<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tasks Report</title>
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
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .company-logo {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px 0;
        }
        .company-logo img {
            width: 210px;
            height: auto;
        }
        .company-logo .logo-text {
            color: white;
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0 5px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .company-logo .logo-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            margin: 0;
            font-weight: 300;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        .badge-success { background-color: #28c76f; }
        .badge-warning { background-color: #ff9f43; }
        .badge-info { background-color: #17a2b8; }
        .badge-danger { background-color: #dc3545; }
        .filters-box {
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .filters-box strong {
            color: #4472C4;
        }
    </style>
</head>
<body>
    <!-- Company Logo Header -->
    <div class="company-logo">
        @if(file_exists(public_path('DAssets/logo-blue.webp')))
            <img src="{{ public_path('DAssets/logo-blue.webp') }}" alt="Company Logo">
        @elseif(file_exists(public_path('uploads/company/logo.png')))
            <img src="{{ public_path('uploads/company/logo.png') }}" alt="Company Logo">
        @elseif(file_exists(public_path('uploads/company/logo.jpg')))
            <img src="{{ public_path('uploads/company/logo.jpg') }}" alt="Company Logo">
        @elseif(file_exists(public_path('uploads/company/logo.svg')))
            <img src="{{ public_path('uploads/company/logo.svg') }}" alt="Company Logo">
        @else
            <!-- Fallback: Text-based logo -->
            <div class="logo-text">🏢 ORION CONTRACTING</div>
        @endif
        <div class="logo-subtitle">Project Management & Design Solutions</div>
    </div>

    <div class="header">
        <h1>Tasks Report</h1>
        <div class="subtitle">
            Generated on {{ now()->format('F d, Y \a\t H:i') }}
        </div>
    </div>

    <!-- Applied Filters -->
    @if(!empty(array_filter($filters)))
    <div class="filters-box">
        <strong>Applied Filters:</strong>
        @if(!empty($filters['project_id']))
            Project: {{ $filters['project_name'] ?? 'Selected' }} |
        @endif
        @if(!empty($filters['user_id']))
            User: {{ $filters['user_name'] ?? 'Selected' }} |
        @endif
        @if(!empty($filters['status']))
            Status: {{ implode(', ', array_map('ucfirst', $filters['status'])) }} |
        @endif
        @if(!empty($filters['priority']))
            Priority: {{ implode(', ', array_map('ucfirst', $filters['priority'])) }} |
        @endif
        @if(!empty($filters['date_from']))
            From: {{ \Carbon\Carbon::parse($filters['date_from'])->format('M d, Y') }} |
        @endif
        @if(!empty($filters['date_to']))
            To: {{ \Carbon\Carbon::parse($filters['date_to'])->format('M d, Y') }}
        @endif
    </div>
    @endif

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-row">
            <div class="stat-cell">
                <div class="stat-value">{{ $taskReport['total_tasks'] ?? 0 }}</div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-cell">
                <div class="stat-value" style="color: #28c76f;">{{ $taskReport['completed_tasks'] ?? 0 }}</div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-cell">
                <div class="stat-value" style="color: #ff9f43;">{{ $taskReport['in_progress_tasks'] ?? 0 }}</div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-cell">
                <div class="stat-value" style="color: #dc3545;">{{ $taskReport['overdue_tasks'] ?? 0 }}</div>
                <div class="stat-label">Overdue</div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="stats-grid">
        <div class="stat-row">
            <div class="stat-cell">
                <div class="stat-value">{{ $taskReport['completion_rate'] ?? 0 }}%</div>
                <div class="stat-label">Completion Rate</div>
            </div>
            <div class="stat-cell">
                <div class="stat-value">{{ $taskReport['average_completion_time'] ?? 0 }}</div>
                <div class="stat-label">Avg. Completion (hours)</div>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    @if(isset($tasks) && count($tasks) > 0)
    <div class="section-title">Tasks List</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Task</th>
                <th style="width: 15%;">Project</th>
                <th style="width: 15%;">Assignee</th>
                <th style="width: 15%;">Status & Priority</th>
                <th style="width: 10%;">Start Date</th>
                <th style="width: 10%;">Due Date</th>
                <th style="width: 10%;">Duration</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
            <tr>
                <td>
                    <strong>{{ $task->title }}</strong>
                    @if($task->description)
                        <br><small style="color: #666;">{{ Str::limit($task->description, 50) }}</small>
                    @endif
                </td>
                <td>
                    @if($task->project)
                        {{ $task->project->name }}
                    @else
                        <span style="color: #999;">No Project</span>
                    @endif
                </td>
                <td>
                    @if($task->assignee)
                        <strong>{{ $task->assignee->name }}</strong>
                        <br><small style="color: #666;">{{ $task->assignee->email }}</small>
                    @else
                        <span style="color: #999;">Unassigned</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : ($task->status === 'pending' ? 'info' : 'danger')) }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                    <br>
                    <span class="badge badge-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                </td>
                <td>
                    @if($task->start_date)
                        {{ \Carbon\Carbon::parse($task->start_date)->format('M d, Y') }}
                        <br><small style="color: #666;">{{ \Carbon\Carbon::parse($task->start_date)->format('H:i') }}</small>
                    @else
                        <span style="color: #999;">-</span>
                    @endif
                </td>
                <td>
                    @if($task->due_date)
                        {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                        <br><small style="color: #666;">{{ \Carbon\Carbon::parse($task->due_date)->format('H:i') }}</small>
                        @if($task->is_overdue)
                            <br><small style="color: #dc3545; font-weight: bold;">Overdue</small>
                        @endif
                    @else
                        <span style="color: #999;">-</span>
                    @endif
                </td>
                <td>
                    @if($task->start_date && $task->due_date)
                        @php
                            $startDate = \Carbon\Carbon::parse($task->start_date);
                            $dueDate = \Carbon\Carbon::parse($task->due_date);
                            $duration = $startDate->diffInDays($dueDate);
                            $isSameDay = $startDate->isSameDay($dueDate);
                        @endphp
                        @if($isSameDay)
                            <span style="color: #28c76f; font-weight: bold;">Same day</span>
                        @else
                            {{ $duration }} {{ $duration == 1 ? 'day' : 'days' }}
                        @endif
                    @else
                        <span style="color: #999;">N/A</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 40px; color: #6c757d; background-color: #f8f9fa; border-radius: 5px;">
        <div style="font-size: 48px; margin-bottom: 20px;">📋</div>
        <h3>No Tasks Found</h3>
        <p>There are no tasks matching the selected filters.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated by OrionDesigners Project Management System</p>
        <p>For internal use only | {{ now()->format('Y') }} © OrionDesigners</p>
    </div>
</body>
</html>
