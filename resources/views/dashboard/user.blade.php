@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <!-- Welcome Header -->
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Welcome back, {{ $userData['user']->name }}!</h5>
                            <p class="mb-4">Here's your personal task overview and progress summary.</p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                                    <i class="bx bx-task me-1"></i>View All Tasks
                                </a>
                                <a href="{{ route('tasks.create') }}" class="btn btn-outline-primary">
                                    <i class="bx bx-plus me-1"></i>New Task
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <div class="avatar avatar-md">
                                <span class="avatar-initial rounded bg-label-primary">
                                    {{ substr($userData['user']->name, 0, 1) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Ranking Cards -->
    <div class="row mb-4">
        <!-- Overall Ranking -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <div class="avatar-initial rounded bg-label-{{ $userData['rankings']['overall']['user_ranking']['rank'] <= 3 ? 'success' : ($userData['rankings']['overall']['user_ranking']['rank'] <= 10 ? 'warning' : 'primary') }}">
                                    @if($userData['rankings']['overall']['user_ranking']['rank'] == 1)
                                        <i class="bx bx-trophy text-white"></i>
                                    @elseif($userData['rankings']['overall']['user_ranking']['rank'] == 2)
                                        <i class="bx bx-medal text-white"></i>
                                    @elseif($userData['rankings']['overall']['user_ranking']['rank'] == 3)
                                        <i class="bx bx-award text-white"></i>
                                    @else
                                        <i class="bx bx-user text-white"></i>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Overall Ranking</h6>
                                <div class="d-flex align-items-center">
                                    <span class="h4 mb-0 me-2 text-{{ $userData['rankings']['overall']['user_ranking']['rank'] <= 3 ? 'success' : ($userData['rankings']['overall']['user_ranking']['rank'] <= 10 ? 'warning' : 'primary') }}">
                                        #{{ $userData['rankings']['overall']['user_ranking']['rank'] }}
                                    </span>
                                    <small class="text-muted">of {{ $userData['rankings']['overall']['total_users'] }} users</small>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-1 text-{{ $userData['rankings']['overall']['user_ranking']['performance_score'] >= 80 ? 'success' : ($userData['rankings']['overall']['user_ranking']['performance_score'] >= 60 ? 'warning' : 'danger') }}">
                                {{ $userData['rankings']['overall']['user_ranking']['performance_score'] }}%
                            </div>
                            <small class="text-muted">Performance Score</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Completion Rate</small>
                            <small class="text-muted">{{ $userData['rankings']['overall']['user_ranking']['completion_rate'] }}%</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $userData['rankings']['overall']['user_ranking']['completion_rate'] >= 80 ? 'success' : ($userData['rankings']['overall']['user_ranking']['completion_rate'] >= 60 ? 'warning' : 'danger') }}"
                                 style="width: {{ $userData['rankings']['overall']['user_ranking']['completion_rate'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Ranking -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <div class="avatar-initial rounded bg-label-{{ $userData['rankings']['monthly']['user_ranking']['rank'] <= 3 ? 'success' : ($userData['rankings']['monthly']['user_ranking']['rank'] <= 10 ? 'warning' : 'primary') }}">
                                    @if($userData['rankings']['monthly']['user_ranking']['rank'] == 1)
                                        <i class="bx bx-trophy text-white"></i>
                                    @elseif($userData['rankings']['monthly']['user_ranking']['rank'] == 2)
                                        <i class="bx bx-medal text-white"></i>
                                    @elseif($userData['rankings']['monthly']['user_ranking']['rank'] == 3)
                                        <i class="bx bx-award text-white"></i>
                                    @else
                                        <i class="bx bx-calendar text-white"></i>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">This Month</h6>
                                <div class="d-flex align-items-center">
                                    <span class="h4 mb-0 me-2 text-{{ $userData['rankings']['monthly']['user_ranking']['rank'] <= 3 ? 'success' : ($userData['rankings']['monthly']['user_ranking']['rank'] <= 10 ? 'warning' : 'primary') }}">
                                        #{{ $userData['rankings']['monthly']['user_ranking']['rank'] }}
                                    </span>
                                    <small class="text-muted">of {{ $userData['rankings']['monthly']['total_users'] }} users</small>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-1 text-{{ $userData['rankings']['monthly']['user_ranking']['performance_score'] >= 80 ? 'success' : ($userData['rankings']['monthly']['user_ranking']['performance_score'] >= 60 ? 'warning' : 'danger') }}">
                                {{ $userData['rankings']['monthly']['user_ranking']['performance_score'] }}%
                            </div>
                            <small class="text-muted">Performance Score</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Tasks Completed</small>
                            <small class="text-muted">{{ $userData['rankings']['monthly']['user_ranking']['completed_tasks'] }}/{{ $userData['rankings']['monthly']['user_ranking']['total_tasks'] }}</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $userData['rankings']['monthly']['user_ranking']['completion_rate'] >= 80 ? 'success' : ($userData['rankings']['monthly']['user_ranking']['completion_rate'] >= 60 ? 'warning' : 'danger') }}"
                                 style="width: {{ $userData['rankings']['monthly']['user_ranking']['completion_rate'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Statistics Cards -->
    <div class="row">
        <!-- Total Tasks -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-task text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Tasks</span>
                    <h3 class="card-title mb-2">{{ $userData['task_stats']['total'] }}</h3>
                    <small class="text-primary fw-semibold">{{ $userData['task_stats']['completion_rate'] }}% completed</small>
                </div>
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Completed</span>
                    <h3 class="card-title mb-2">{{ $userData['task_stats']['completed'] }}</h3>
                    <small class="text-success fw-semibold">Tasks done</small>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-time text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">In Progress</span>
                    <h3 class="card-title mb-2">{{ $userData['task_stats']['in_progress'] }}</h3>
                    <small class="text-warning fw-semibold">Currently working</small>
                </div>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <i class="bx bx-error text-danger" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Overdue</span>
                    <h3 class="card-title mb-2">{{ $userData['task_stats']['overdue'] }}</h3>
                    <small class="text-danger fw-semibold">Need attention</small>
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
                    <h5 class="card-title mb-0">My Tasks by Status</h5>
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
                    <h5 class="card-title mb-0">My Tasks by Priority</h5>
                </div>
                <div class="card-body">
                    <canvas id="taskPriorityChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance and Activity Row -->
    <div class="row">
        <!-- Performance Metrics -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <h4 class="text-primary mb-0">{{ $userData['performance']['avg_completion_time'] }}</h4>
                                <small class="text-muted">Avg. Days to Complete</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <h4 class="text-success mb-0">{{ $userData['performance']['completed_this_week'] }}</h4>
                                <small class="text-muted">Completed This Week</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                <h4 class="text-info mb-0">{{ $userData['performance']['tasks_this_week'] }}</h4>
                                <small class="text-muted">Tasks This Week</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                <h4 class="text-warning mb-0">{{ $userData['performance']['completed_this_month'] }}</h4>
                                <small class="text-muted">Completed This Month</small>
                            </div>
                        </div>
                    </div>
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
                    @forelse($userData['recent_activity'] as $task)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-task"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $task->title }}</h6>
                                    <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                </div>
                                <div class="user-progress">
                                    <span class="badge bg-label-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : 'primary') }}">
                                        {{ ucfirst($task->status) }}
                                    </span>
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
                    @forelse($userData['upcoming_tasks'] as $task)
                        <div class="d-flex align-items-center mb-3 p-2 bg-warning bg-opacity-10 rounded">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-time"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $task->title }}</h6>
                                    <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                </div>
                                <div class="user-progress">
                                    <small class="text-warning fw-semibold">{{ $task->due_date->format('M d') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $task->due_date->diffForHumans() }}</small>
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
                    @forelse($userData['overdue_tasks'] as $task)
                        <div class="d-flex align-items-center mb-3 p-2 bg-danger bg-opacity-10 rounded">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="bx bx-error"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $task->title }}</h6>
                                    <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                </div>
                                <div class="user-progress">
                                    <small class="text-danger fw-semibold">{{ $task->due_date->format('M d') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $task->due_date->diffForHumans() }}</small>
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

    <!-- Recent Tasks and Notifications -->
    <div class="row">
        <!-- Recent Tasks -->
        <div class="col-lg-8 col-md-8 col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Tasks</h5>
                    <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($userData['recent_tasks'] as $task)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded bg-label-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : 'primary') }}">
                                                        <i class="bx bx-task"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ Str::limit($task->title, 30) }}</h6>
                                                    <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info">{{ $task->project->name ?? 'No Project' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : 'primary') }}">
                                                {{ ucfirst($task->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'success') }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($task->due_date)
                                                <small class="text-muted">{{ $task->due_date->format('M d, Y') }}</small>
                                            @else
                                                <small class="text-muted">No due date</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No tasks found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-lg-4 col-md-4 col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Notifications</h5>
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @forelse($userData['notifications'] as $notification)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-{{ $notification->read ? 'secondary' : 'primary' }}">
                                    <i class="bx bx-bell"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $notification->title }}</h6>
                                    <small class="text-muted">{{ Str::limit($notification->message, 50) }}</small>
                                </div>
                                <div class="user-progress">
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No notifications</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Trend Chart -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Weekly Completion Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="weeklyTrendChart" width="400" height="100"></canvas>
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
    const taskStatusData = @json($userData['tasks_by_status']);

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
    const taskPriorityData = @json($userData['tasks_by_priority']);

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

    // Weekly Trend Chart
    const weeklyTrendCtx = document.getElementById('weeklyTrendChart').getContext('2d');
    const weeklyTrendData = @json($userData['weekly_trend']);

    new Chart(weeklyTrendCtx, {
        type: 'line',
        data: {
            labels: weeklyTrendData.map(item => item.week),
            datasets: [{
                label: 'Completed Tasks',
                data: weeklyTrendData.map(item => item.completed),
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

    // Ensure handleNotificationClick function is available on dashboard
    if (typeof window.handleNotificationClick === 'undefined') {
        window.handleNotificationClick = function(notificationId, viewUrl) {
            console.log('handleNotificationClick called with:', notificationId, viewUrl);

            // Mark as read using unified notification system
            fetch(`{{ url('notifications') }}/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            }).then(() => {
                console.log('Notification marked as read:', notificationId);
                // Refresh notification counts if functions are available
                if (typeof fetchEmailCount === 'function') fetchEmailCount();
                if (typeof fetchTaskCount === 'function') fetchTaskCount();
                if (typeof fetchBottomNotifications === 'function') fetchBottomNotifications();
            }).catch(error => {
                console.error('Error marking notification as read:', error);
            });

            // Navigate to URL if provided
            if (viewUrl && viewUrl.trim() !== '' && viewUrl !== '#') {
                console.log('Navigating to:', viewUrl);
                window.location.href = viewUrl;
            } else {
                console.log('No valid URL provided for navigation');
            }
        };
    }
</script>
@endpush
@endsection
