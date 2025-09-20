@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Manager Dashboard</h5>
                            <p class="mb-4">Last updated: {{ now()->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row">
        <!-- Total Users -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-user text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Users</span>
                    <h3 class="card-title mb-2">{{ $data['overview']['total_users'] }}</h3>
                    <small class="text-success fw-semibold">{{ $data['overview']['active_users'] }} active</small>
                </div>
            </div>
        </div>

        <!-- Total Tasks -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-task text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Tasks</span>
                    <h3 class="card-title mb-2">{{ $data['overview']['total_tasks'] }}</h3>
                    <small class="text-primary fw-semibold">{{ $data['overview']['completion_rate'] }}% completed</small>
                </div>
            </div>
        </div>

        <!-- Projects -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-folder text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Projects</span>
                    <h3 class="card-title mb-2">{{ $data['overview']['total_projects'] }}</h3>
                    <small class="text-warning fw-semibold">{{ $data['project_stats']['active'] }} active</small>
                </div>
            </div>
        </div>

        <!-- Weekly Completed -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-check-circle text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">This Week</span>
                    <h3 class="card-title mb-2">{{ $data['overview']['weekly_completed'] }}</h3>
                    <small class="text-info fw-semibold">tasks completed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Task Status Chart -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tasks by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="taskStatusChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Task Priority Chart -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tasks by Priority</h5>
                </div>
                <div class="card-body">
                    <canvas id="taskPriorityChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance and Activity Row -->
    <div class="row">
        <!-- Top Performers -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Performers</h5>
                </div>
                <div class="card-body">
                    @forelse($data['top_performers'] as $index => $user)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">{{ $index + 1 }}</span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                    <small class="text-muted">{{ $user->completed_tasks_count }} completed</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    <h6 class="mb-0">{{ $user->completion_rate }}%</h6>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No performance data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    @forelse($data['recent_activity'] as $task)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-task"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $task->title }}</h6>
                                    <small class="text-muted">{{ $task->assignee->name ?? 'Unassigned' }} • {{ $task->project->name }}</small>
                                </div>
                                <div class="user-progress">
                                    <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No recent activity</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Due Dates and Overdue Tasks -->
    <div class="row">
        <!-- Upcoming Due Dates -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upcoming Due Dates</h5>
                </div>
                <div class="card-body">
                    @forelse($data['upcoming_due_dates'] as $task)
                        <div class="d-flex align-items-center mb-3 p-2 bg-warning bg-opacity-10 rounded">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-time"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $task->title }}</h6>
                                    <small class="text-muted">{{ $task->assignee->name ?? 'Unassigned' }} • {{ $task->project->name }}</small>
                                </div>
                                <div class="user-progress">
                                    <small class="text-warning fw-semibold">{{ $task->due_date->format('M d') }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No upcoming due dates</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Overdue Tasks</h5>
                </div>
                <div class="card-body">
                    @forelse($data['overdue_tasks'] as $task)
                        <div class="d-flex align-items-center mb-3 p-2 bg-danger bg-opacity-10 rounded">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="bx bx-error"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $task->title }}</h6>
                                    <small class="text-muted">{{ $task->assignee->name ?? 'Unassigned' }} • {{ $task->project->name }}</small>
                                </div>
                                <div class="user-progress">
                                    <small class="text-danger fw-semibold">{{ $task->due_date->format('M d') }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No overdue tasks</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Task Distribution by Project -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Task Distribution by Project</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($data['tasks_by_project'] as $project)
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">{{ $project->name }}</h6>
                                        <h3 class="text-primary mb-0">{{ $project->tasks_count }}</h3>
                                        <small class="text-muted">tasks</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">No project data available</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend Chart -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Task Completion Trend (Last 12 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendChart" width="400" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-6 mb-3">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-0">{{ $data['task_stats']['completed'] }}</h3>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6 mb-3">
                            <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                <h3 class="text-warning mb-0">{{ $data['task_stats']['in_progress'] }}</h3>
                                <small class="text-muted">In Progress</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6 mb-3">
                            <div class="text-center p-3 bg-secondary bg-opacity-10 rounded">
                                <h3 class="text-secondary mb-0">{{ $data['task_stats']['pending'] }}</h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6 mb-3">
                            <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                                <h3 class="text-danger mb-0">{{ $data['task_stats']['overdue'] }}</h3>
                                <small class="text-muted">Overdue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Task Status Chart
    const taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
    const taskStatusData = @json($data['tasks_by_status']);

    new Chart(taskStatusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(taskStatusData).map(status => status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')),
            datasets: [{
                data: Object.values(taskStatusData),
                backgroundColor: [
                    '#3B82F6', // blue
                    '#10B981', // green
                    '#F59E0B', // yellow
                    '#EF4444', // red
                    '#8B5CF6', // purple
                    '#06B6D4', // cyan
                    '#84CC16'  // lime
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Task Priority Chart
    const taskPriorityCtx = document.getElementById('taskPriorityChart').getContext('2d');
    const taskPriorityData = @json($data['tasks_by_priority']);

    new Chart(taskPriorityCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(taskPriorityData).map(priority => priority.charAt(0).toUpperCase() + priority.slice(1)),
            datasets: [{
                label: 'Tasks',
                data: Object.values(taskPriorityData),
                backgroundColor: [
                    '#10B981', // green - low
                    '#3B82F6', // blue - normal
                    '#06B6D4', // cyan - medium
                    '#F59E0B', // yellow - high
                    '#EF4444', // red - urgent
                    '#7C2D12'  // dark red - critical
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Monthly Trend Chart
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    const monthlyTrendData = @json($data['monthly_trend']);

    // Create array of last 12 months with data
    const months = [];
    const completedData = [];
    const currentDate = new Date();

    for (let i = 11; i >= 0; i--) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
        const monthKey = date.toISOString().slice(0, 7);
        const monthName = date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });

        months.push(monthName);
        completedData.push(monthlyTrendData[monthKey] || 0);
    }

    new Chart(monthlyTrendCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Completed Tasks',
                data: completedData,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
@endsection
