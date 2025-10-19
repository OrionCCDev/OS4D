@extends('layouts.app')

@section('content')
<style>
    .competition-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .competition-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .medal-animation {
        animation: pulse 2s infinite;
    }

    .gold-medal {
        animation: goldGlow 3s ease-in-out infinite alternate;
    }

    .silver-medal {
        animation: silverShine 2.5s ease-in-out infinite alternate;
    }

    .bronze-medal {
        animation: bronzeGlow 2s ease-in-out infinite alternate;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    @keyframes goldGlow {
        0% { color: #ffd700; text-shadow: 0 0 5px #ffd700; }
        100% { color: #ffed4e; text-shadow: 0 0 15px #ffd700; }
    }

    @keyframes silverShine {
        0% { color: #c0c0c0; text-shadow: 0 0 3px #c0c0c0; }
        100% { color: #e8e8e8; text-shadow: 0 0 10px #c0c0c0; }
    }

    @keyframes bronzeGlow {
        0% { color: #cd7f32; text-shadow: 0 0 3px #cd7f32; }
        100% { color: #daa520; text-shadow: 0 0 8px #cd7f32; }
    }

    .rank-badge {
        position: relative;
        overflow: hidden;
    }

    .rank-badge::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
        transform: rotate(45deg);
        transition: all 0.6s;
        opacity: 0;
    }

    .rank-badge:hover::before {
        animation: shine 0.6s ease-in-out;
    }

    @keyframes shine {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); opacity: 0; }
        50% { opacity: 1; }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); opacity: 0; }
    }
</style>
<div class="container flex-grow-1 container-p-y">
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
        <!-- Top 3 Competition Board -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card competition-card card-gradient">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 text-white">
                        <i class="bx bx-trophy me-2 text-warning medal-animation"></i>Top 3 Competition
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-info">
                            <i class="bx bx-calendar me-1"></i>This Month
                        </span>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshCompetition()" title="Refresh Competition Data">
                            <i class="bx bx-refresh"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($data['monthly_top_performers']->count() > 0)
                        @foreach($data['monthly_top_performers']->take(3) as $index => $user)
                            @php
                                $medalClass = '';
                                $medalIcon = '';
                                $rankColor = '';
                                $bgClass = '';

                                switch($index) {
                                    case 0:
                                        $medalClass = 'gold-medal';
                                        $medalIcon = 'bx-trophy';
                                        $rankColor = 'text-warning';
                                        $bgClass = 'bg-warning bg-opacity-10';
                                        $rankBadge = 'bg-warning';
                                        break;
                                    case 1:
                                        $medalClass = 'silver-medal';
                                        $medalIcon = 'bx-medal';
                                        $rankColor = 'text-secondary';
                                        $bgClass = 'bg-secondary bg-opacity-10';
                                        $rankBadge = 'bg-secondary';
                                        break;
                                    case 2:
                                        $medalClass = 'bronze-medal';
                                        $medalIcon = 'bx-award';
                                        $rankColor = 'text-warning';
                                        $bgClass = 'bg-warning bg-opacity-10';
                                        $rankBadge = 'bg-warning';
                                        break;
                                }
                            @endphp

                            <div class="d-flex align-items-center mb-3 p-3 rounded {{ $bgClass }}">
                                <div class="avatar flex-shrink-0 me-3 position-relative">
                                    @if($index < 3)
                                        <div class="position-absolute top-0 start-100 translate-middle">
                                            <i class="bx {{ $medalIcon }} {{ $medalClass }}" style="font-size: 24px;"></i>
                                        </div>
                                    @endif
                                    <span class="avatar-initial rounded-circle {{ $rankBadge }} text-white fw-bold rank-badge" style="font-size: 18px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        {{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0 {{ $rankColor }} fw-bold">
                                            {{ $user->name }}
                                            @if($index === 0)
                                                <i class="bx bx-crown text-warning ms-1 gold-medal" style="font-size: 16px;"></i>
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bx bx-task me-1"></i>{{ $user->completed_tasks_count }} completed tasks
                                            @if($user->total_tasks_count > 0)
                                                <span class="ms-2">
                                                    <i class="bx bx-target-lock me-1"></i>{{ $user->total_tasks_count }} total
                                                </span>
                                            @endif
                                        </small>
                                    </div>
                                    <div class="user-progress d-flex flex-column align-items-end">
                                        <h6 class="mb-0 {{ $rankColor }} fw-bold">{{ $user->completion_rate }}%</h6>
                                        <small class="text-muted">Completion Rate</small>
                                        @if($index === 0)
                                            <small class="text-success fw-semibold">
                                                <i class="bx bx-star me-1"></i>Champion
                                            </small>
                                        @elseif($index === 1)
                                            <small class="text-info fw-semibold">
                                                <i class="bx bx-medal me-1"></i>Runner-up
                                            </small>
                                        @elseif($index === 2)
                                            <small class="text-warning fw-semibold">
                                                <i class="bx bx-award me-1"></i>Third Place
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if($data['monthly_top_performers']->count() > 3)
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Showing top 3 of {{ $data['monthly_top_performers']->count() }} performers
                                </small>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-trophy text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-2">No performance data available</p>
                            <small class="text-muted">Start assigning tasks to see competition results</small>
                        </div>
                    @endif
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

    // Competition refresh function
    function refreshCompetition() {
        const refreshBtn = document.querySelector('button[onclick="refreshCompetition()"]');
        const icon = refreshBtn.querySelector('i');

        // Add loading animation
        icon.classList.add('bx-spin');
        refreshBtn.disabled = true;

        // Simulate refresh (in real implementation, this would make an AJAX call)
        setTimeout(() => {
            // Remove loading animation
            icon.classList.remove('bx-spin');
            refreshBtn.disabled = false;

            // Show success message
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bx bx-check-circle me-2"></i>Competition data refreshed!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(toast);

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', () => {
                document.body.removeChild(toast);
            });

            // In a real implementation, you would reload the page or update the data via AJAX
            // window.location.reload();
        }, 1500);
    }
</script>
@endpush
@endsection
