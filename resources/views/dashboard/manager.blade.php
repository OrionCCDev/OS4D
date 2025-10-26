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

    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #e9ecef, #dee2e6);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        padding-left: 60px;
    }

    .timeline-marker {
        position: absolute;
        left: 20px;
        top: 5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 2;
    }

    .timeline-content {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .timeline-content:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-1px);
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
                            <h5 class="card-title text-primary fw-bold">Manager Dashboard</h5>
                            <p class="mb-4 text-muted">Last updated: {{ now()->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Statistics - Moved to top -->
    <div class="row mb-4">
        <!-- Total Users -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-user text-primary"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0 text-dark fw-semibold">{{ $data['overview']['total_users'] }}</h6>
                            <small class="text-muted">Total Users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Tasks -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-task text-success"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0 text-dark fw-semibold">{{ $data['overview']['total_tasks'] }}</h6>
                            <small class="text-muted">Total Tasks</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Projects -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-folder text-warning"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0 text-dark fw-semibold">{{ $data['overview']['total_projects'] }}</h6>
                            <small class="text-muted">Total Projects</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="col-lg-3 col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-trending-up text-info"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0 text-dark fw-semibold">{{ $data['overview']['completion_rate'] }}%</h6>
                            <small class="text-muted">Completion Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent Tasks Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0 text-dark fw-semibold">
                            <i class="bx bx-error-circle me-2 text-danger"></i>Urgent Tasks
                        </h5>
                        <small class="text-muted">Tasks approaching or exceeding due date</small>
                </div>
                    <small class="text-muted">{{ $data['urgent_tasks']->total() }} urgent tasks</small>
                </div>
                <div class="card-body p-0">
                    @if($data['urgent_tasks']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-dark fw-semibold">Task</th>
                                        <th class="text-dark fw-semibold">Status</th>
                                        <th class="text-dark fw-semibold">Priority</th>
                                        <th class="text-dark fw-semibold">Assignee</th>
                                        <th class="text-dark fw-semibold">Due Date</th>
                                        <th class="text-dark fw-semibold">Urgency</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['urgent_tasks'] as $task)
                                    @php
                                        $isOverdue = $task->is_overdue;

                                        // Calculate total hours difference
                                        $totalHours = now()->diffInHours($task->due_date, false);

                                        // Calculate days and remaining hours
                                        if ($isOverdue) {
                                            $days = abs(floor($totalHours / 24));
                                            $hours = abs($totalHours % 24);
                                            $urgencyText = 'Overdue by ';
                                            if ($days > 0) {
                                                $urgencyText .= $days . 'd ';
                                            }
                                            if ($hours > 0 || $days == 0) {
                                                $urgencyText .= $hours . 'h';
                                            }
                                        } else {
                                            $days = floor($totalHours / 24);
                                            $hours = $totalHours % 24;
                                            $urgencyText = 'Due in ';
                                            if ($days > 0) {
                                                $urgencyText .= $days . 'd ';
                                            }
                                            if ($hours > 0 || $days == 0) {
                                                $urgencyText .= $hours . 'h';
                                            }
                                        }

                                        $urgencyClass = $isOverdue ? 'danger' : ($days <= 2 ? 'warning' : 'info');
                                    @endphp
                                    <tr style="cursor: pointer; transition: background-color 0.2s;"
                                        onclick="window.location.href='{{ route('tasks.show', $task->id) }}'"
                                        onmouseover="this.style.backgroundColor='#f8f9fa'"
                                        onmouseout="this.style.backgroundColor='transparent'">
                                        <td>
                                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                                    @if($isOverdue)
                                                        <i class="bx bx-error-circle text-danger" style="font-size: 1.2rem;"></i>
                                                    @else
                                                        <i class="bx bx-time-five text-warning" style="font-size: 1.2rem;"></i>
                                                    @endif
                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-dark fw-semibold">{{ Str::limit($task->title, 40) }}</h6>
                                                    <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                </div>
                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $task->status_badge_class }} text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 600;">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $task->priority_badge_class }} text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 600;">
                                                {{ ucfirst($task->priority ?? 'Normal') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($task->assignee)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-xs me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr($task->assignee->name, 0, 1) }}
                                </span>
                            </div>
                                                    <span class="text-dark fw-semibold">{{ $task->assignee->name }}</span>
                                </div>
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-dark fw-semibold {{ $isOverdue ? 'text-danger' : '' }}">
                                                {{ $task->due_date->format('M j, Y') }}
                                            </span>
                                            <br><small class="text-muted">{{ $task->due_date->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $urgencyClass }} text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 600;">
                                                @if($isOverdue)
                                                    <i class="bx bx-error-circle me-1"></i>
                                                @else
                                                    <i class="bx bx-time-five me-1"></i>
                                                @endif
                                                {{ $urgencyText }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                                </div>
                        @if($data['urgent_tasks']->hasPages())
                            <div class="card-footer">
                                {{ $data['urgent_tasks']->appends(request()->except('urgent_page'))->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-check-circle fs-1 text-success opacity-50"></i>
                            <p class="text-muted mt-2">No urgent tasks</p>
                            <small class="text-muted">All tasks are on track!</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Tasks by Status List -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark fw-semibold">Tasks by Status</h5>
                    <small class="text-muted">{{ $data['tasks_by_status']->total() }} total tasks</small>
                </div>
                <div class="card-body p-0">
                    @if($data['tasks_by_status']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-dark fw-semibold">Task</th>
                                        <th class="text-dark fw-semibold">Status</th>
                                        <th class="text-dark fw-semibold">Assignee</th>
                                        <th class="text-dark fw-semibold">Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['tasks_by_status'] as $task)
                                    <tr style="cursor: pointer; transition: background-color 0.2s;"
                                        onclick="window.location.href='{{ route('tasks.show', $task->id) }}'"
                                        onmouseover="this.style.backgroundColor='#f8f9fa'"
                                        onmouseout="this.style.backgroundColor='transparent'">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="bx bx-task text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-dark fw-semibold">{{ Str::limit($task->title, 30) }}</h6>
                                                    <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $task->status_badge_class }} text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 600;">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($task->assignee)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-xs me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr($task->assignee->name, 0, 1) }}
                                </span>
                            </div>
                                                    <span class="text-dark fw-semibold">{{ $task->assignee->name }}</span>
                                </div>
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->due_date)
                                                <span class="text-dark fw-semibold {{ $task->is_overdue ? 'text-danger' : '' }}">
                                                    {{ $task->due_date->format('M j, Y') }}
                                                </span>
                                                @if($task->is_overdue)
                                                    <br><small class="text-danger">Overdue</small>
                                                @endif
                                            @else
                                                <span class="text-muted">No due date</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                                </div>
                        @if($data['tasks_by_status']->hasPages())
                            <div class="card-footer">
                                {{ $data['tasks_by_status']->appends(request()->except('status_page'))->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-task fs-1 text-muted opacity-50"></i>
                            <p class="text-muted mt-2">No tasks available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tasks by Priority List -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark fw-semibold">Tasks by Priority</h5>
                    <small class="text-muted">{{ $data['tasks_by_priority']->total() }} total tasks</small>
                </div>
                <div class="card-body p-0">
                    @if($data['tasks_by_priority']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-dark fw-semibold">Task</th>
                                        <th class="text-dark fw-semibold">Priority</th>
                                        <th class="text-dark fw-semibold">Assignee</th>
                                        <th class="text-dark fw-semibold">Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['tasks_by_priority'] as $task)
                                    <tr style="cursor: pointer; transition: background-color 0.2s;"
                                        onclick="window.location.href='{{ route('tasks.show', $task->id) }}'"
                                        onmouseover="this.style.backgroundColor='#f8f9fa'"
                                        onmouseout="this.style.backgroundColor='transparent'">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="bx bx-task text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-dark fw-semibold">{{ Str::limit($task->title, 30) }}</h6>
                                                    <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $task->priority_badge_class }} text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 600;">
                                                {{ ucfirst($task->priority ?? 'Normal') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($task->assignee)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-xs me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr($task->assignee->name, 0, 1) }}
                                </span>
                            </div>
                                                    <span class="text-dark fw-semibold">{{ $task->assignee->name }}</span>
                                </div>
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->due_date)
                                                <span class="text-dark fw-semibold {{ $task->is_overdue ? 'text-danger' : '' }}">
                                                    {{ $task->due_date->format('M j, Y') }}
                                                </span>
                                                @if($task->is_overdue)
                                                    <br><small class="text-danger">Overdue</small>
                                                @endif
                                            @else
                                                <span class="text-muted">No due date</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                                </div>
                        @if($data['tasks_by_priority']->hasPages())
                            <div class="card-footer">
                                {{ $data['tasks_by_priority']->appends(request()->except('priority_page'))->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-task fs-1 text-muted opacity-50"></i>
                            <p class="text-muted mt-2">No tasks available</p>
                        </div>
                    @endif
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
                    <h5 class="card-title mb-0 text-white fw-semibold">
                        <i class="bx bx-trophy me-2 text-warning medal-animation"></i>Top 3 Competition
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-light active" data-period="month" onclick="changeCompetitionPeriod('month')">
                                <i class="bx bx-calendar me-1"></i>This Month
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light" data-period="quarter" onclick="changeCompetitionPeriod('quarter')">
                                <i class="bx bx-calendar me-1"></i>This Quarter
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light" data-period="year" onclick="changeCompetitionPeriod('year')">
                                <i class="bx bx-calendar me-1"></i>This Year
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="competitionContent">
                        @if(count($data['monthly_top_performers']) > 0)
                    <div class="row">
                                @foreach($data['monthly_top_performers'] as $index => $performer)
                                    <div class="col-12 mb-3">
                                        <div class="d-flex align-items-center p-3 rounded-3" style="background: rgba(255,255,255,0.1);">
                                            <div class="rank-badge me-3">
                                                @if($index === 0)
                                                    <i class="bx bx-medal gold-medal" style="font-size: 2rem;"></i>
                                                @elseif($index === 1)
                                                    <i class="bx bx-medal silver-medal" style="font-size: 2rem;"></i>
                                                @else
                                                    <i class="bx bx-medal bronze-medal" style="font-size: 2rem;"></i>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 text-white fw-semibold">{{ $performer->name }}</h6>
                                                <div class="d-flex align-items-center gap-3">
                                                    <small class="text-white-50">
                                                        <i class="bx bx-check-circle me-1"></i>
                                                        {{ $performer->completed_tasks_count ?? 0 }} completed
                                                    </small>
                                                    <small class="text-white-50">
                                                        <i class="bx bx-time me-1"></i>
                                                        {{ $performer->in_progress_tasks_count ?? 0 }} in progress
                                                    </small>
                                                    <small class="text-white-50">
                                                        <i class="bx bx-list-ul me-1"></i>
                                                        {{ $performer->total_tasks_count ?? 0 }} total
                                                    </small>
                                                </div>
                                                @if(isset($performer->rejection_rate) && $performer->rejection_rate > 0)
                                                    <small class="text-warning">
                                                        <i class="bx bx-error-circle me-1"></i>
                                                        {{ number_format($performer->rejection_rate, 1) }}% rejection rate
                                                    </small>
                                                @endif
                                                @if(isset($performer->overdue_rate) && $performer->overdue_rate > 0)
                                                    <small class="text-warning ms-2">
                                                        <i class="bx bx-time-five me-1"></i>
                                                        {{ number_format($performer->overdue_rate, 1) }}% overdue rate
                                                    </small>
                                                @endif
                                                <div class="mt-1">
                                                    <span class="badge bg-success bg-opacity-20 text-dark">
                                                        Performance Score: {{ $performer->monthly_performance_score ?? $performer->performance_score ?? 0 }}
                                                    </span>
                                                    <span class="badge bg-info bg-opacity-20 text-dark ms-1">
                                                        Completion Rate: {{ number_format($performer->completion_rate ?? 0, 1) }}%
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bx bx-trophy fs-1 text-white-50"></i>
                                <p class="text-white-50 mt-2">No performance data available</p>
                                <small class="text-white-50">Start assigning tasks to see competition results</small>
                            </div>
                        @endif
                </div>
            </div>
        </div>
    </div>

        <!-- Recent Activity -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 text-dark fw-semibold">Recent Activity</h5>
                </div>
                <div class="card-body">
                    @if(count($data['recent_activity']) > 0)
                        <div class="timeline">
                            @foreach($data['recent_activity'] as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $activity['type'] ?? 'primary' }}"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1 text-dark fw-semibold">{{ $activity['title'] ?? 'Activity' }}</h6>
                                        <p class="text-muted mb-1 small">{{ $activity['description'] ?? 'No description available' }}</p>
                                        <small class="text-muted">
                                            <i class="bx bx-time me-1"></i>
                                            {{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}
                                            @if(isset($activity['project']))
                                                | <i class="bx bx-folder me-1"></i>{{ $activity['project']['name'] ?? 'Unknown Project' }}
                                            @endif
                                        </small>
                </div>
            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-activity fs-1 text-muted opacity-50"></i>
                            <p class="text-muted mt-2">No recent activity</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0 text-dark fw-semibold">
                            <i class="bx bx-calendar me-2 text-primary"></i>Task Timeline
                        </h5>
                        <small class="text-muted">Tasks starting or due in the next 20 days</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white px-3 py-2 rounded-pill">
                            {{ count($data['timeline_data']['sequential']) }} days with tasks
                        </span>
                        @if(count($data['timeline_data']['sequential']) > 0)
                            <button class="btn btn-sm btn-outline-primary" onclick="toggleTimelineView()">
                                <i class="bx bx-grid-alt me-1"></i>Calendar View
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($data['timeline_data']['sequential']) > 0)
                        <div id="timelineContainer">
                            <!-- Horizontal Timeline View -->
                            <div id="timelineView" class="horizontal-timeline-container">
                                <div class="timeline-header">
                                    <div class="timeline-range">
                                        <span class="timeline-range-text">
                                            Next 20 days: {{ now()->format('M j') }} - {{ now()->addDays(20)->format('M j, Y') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="timeline-chart">
                                    <div class="timeline-axis">
                                        @php
                                            $startDate = now();
                                            $endDate = now()->addDays(20);
                                            $totalDays = 20;
                                        @endphp

                                        @for($i = 0; $i <= $totalDays; $i++)
                                            @php
                                                $currentDate = $startDate->copy()->addDays($i);
                                                $dateKey = $currentDate->format('Y-m-d');
                                                $hasTasks = isset($data['timeline_data']['by_date'][$dateKey]);
                                            @endphp
                                            <div class="timeline-day-marker {{ $hasTasks ? 'has-tasks' : '' }}" data-date="{{ $dateKey }}">
                                                <div class="day-label">{{ $currentDate->format('M j') }}</div>
                                                @if($hasTasks)
                                                    <div class="day-indicator"></div>
                                                @endif
                                            </div>
                                        @endfor
                                    </div>

                                    <div class="timeline-tasks-layer">
                                        @foreach($data['timeline_data']['sequential'] as $dayData)
                                            @foreach($dayData['tasks'] as $task)
                                                @php
                                                    $taskStartDate = $task['start_date'] ? \Carbon\Carbon::parse($task['start_date']) : \Carbon\Carbon::parse($task['due_date']);
                                                    $taskEndDate = $task['due_date'] ? \Carbon\Carbon::parse($task['due_date']) : $taskStartDate->copy()->addDays(1);

                                                    // Calculate position and width
                                                    $startDay = $startDate->diffInDays($taskStartDate);
                                                    $duration = $taskStartDate->diffInDays($taskEndDate);

                                                    $leftPercent = max(0, ($startDay / $totalDays) * 100);
                                                    $widthPercent = max(5, ($duration / $totalDays) * 100);
                                                @endphp
                                                <div class="timeline-task-bar"
                                                     style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%;"
                                                     data-task-id="{{ $task['id'] }}"
                                                     onclick="window.location.href='{{ route('tasks.show', $task['id']) }}'">
                                                    <div class="task-bar-gradient"></div>
                                                    <div class="task-end-marker"></div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Task Details Panel -->
                                <div class="timeline-details-panel">
                                    <div class="task-details-list">
                                        @foreach($data['timeline_data']['sequential'] as $dayData)
                                            @foreach($dayData['tasks'] as $task)
                                                <div class="task-detail-card" data-task-id="{{ $task['id'] }}">
                                                    <div class="task-detail-header">
                                                        <div class="task-date">{{ \Carbon\Carbon::parse($task['start_date'] ?: $task['due_date'])->format('M j') }}</div>
                                                        <div class="task-title">{{ $task['title'] }}</div>
                                                        <div class="task-status">
                                                            <span class="badge {{ $task['status_badge_class'] }} text-white px-2 py-1 rounded-pill" style="font-size: 10px;">
                                                                {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Calendar Grid View -->
                            <div id="calendarView" class="calendar-container" style="display: none;">
                                <div class="calendar-grid">
                                    @foreach($data['timeline_data'] as $dayData)
                                        <div class="calendar-day">
                                            <div class="calendar-day-header">
                                                <h6 class="mb-0 text-dark fw-semibold">{{ $dayData['date']->format('j') }}</h6>
                                                <small class="text-muted">{{ $dayData['date']->format('M') }}</small>
                                            </div>
                                            <div class="calendar-tasks">
                                                @foreach($dayData['tasks'] as $task)
                                                    <div class="calendar-task" onclick="window.location.href='{{ route('tasks.show', $task['id']) }}'">
                                                        <div class="task-indicator {{ $task['status_badge_class'] }}"></div>
                                                        <div class="task-info">
                                                            <div class="task-title">{{ Str::limit($task['title'], 20) }}</div>
                                                            <div class="task-project">{{ Str::limit($task['project_name'], 15) }}</div>
                                                            @if($task['start_date'])
                                                                <div class="task-date-info">
                                                                    <small class="text-primary">Start: {{ \Carbon\Carbon::parse($task['start_date'])->format('M j') }}</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-calendar fs-1 text-muted opacity-50"></i>
                            <p class="text-muted mt-3">No tasks scheduled for the next 20 days</p>
                            <small class="text-muted">Tasks with start dates or due dates will appear here</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin: 0 0 8px 0;
    font-size: 14px;
    font-weight: 600;
}

.timeline-text {
    margin: 0 0 8px 0;
    font-size: 13px;
    line-height: 1.4;
}

.timeline-meta {
    font-size: 12px;
    color: #6c757d;
}

/* Horizontal Timeline Styles */
.horizontal-timeline-container {
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    margin: 20px;
}

.timeline-header {
    margin-bottom: 20px;
}

.timeline-range-text {
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
}

.timeline-chart {
    position: relative;
    height: 120px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    overflow: hidden;
}

.timeline-axis {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: #2c3e50;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 10px;
}

.timeline-day-marker {
    position: relative;
    flex: 1;
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.timeline-day-marker.has-tasks .day-indicator {
    width: 8px;
    height: 8px;
    background: #007bff;
    border-radius: 50%;
    margin-top: 5px;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.day-label {
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.timeline-tasks-layer {
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 60px;
}

.timeline-task-bar {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    height: 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.timeline-task-bar:hover {
    height: 12px;
    transform: translateY(-50%) scaleY(1.2);
}

.task-bar-gradient {
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, #007bff 0%, #28a745 30%, #ffc107 60%, #fd7e14 100%);
    border-radius: 4px;
    position: relative;
}

.task-end-marker {
    position: absolute;
    right: -6px;
    top: 50%;
    transform: translateY(-50%);
    width: 12px;
    height: 12px;
    background: #007bff;
    border: 2px solid #fff;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.timeline-details-panel {
    margin-top: 20px;
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.task-details-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.task-detail-card {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 10px;
    min-width: 200px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.task-detail-card:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.task-detail-header {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.task-detail-header .task-date {
    font-size: 11px;
    color: #6c757d;
    font-weight: 500;
}

.task-detail-header .task-title {
    font-size: 13px;
    font-weight: 600;
    color: #2c3e50;
}

.task-detail-header .task-status {
    margin-top: 5px;
}

.timeline-day-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.timeline-date h6 {
    color: #2c3e50;
    font-weight: 700;
    font-size: 18px;
    margin-bottom: 5px;
}

.timeline-date small {
    color: #6c757d;
    font-size: 14px;
    font-weight: 400;
}

.timeline-day-count .badge {
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
}

.timeline-tasks {
    display: grid;
    gap: 12px;
}

.timeline-task-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 15px;
}

.timeline-task-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    border-color: #007bff;
}

.task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.task-title h6 {
    color: #2c3e50;
    font-weight: 700;
    font-size: 16px;
    line-height: 1.3;
    margin-bottom: 5px;
}

.task-title small {
    color: #6c757d;
    font-size: 13px;
    font-weight: 400;
}

.task-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.task-card-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.task-assignee {
    display: flex;
    align-items: center;
}

.task-assignee .avatar {
    width: 32px;
    height: 32px;
    font-size: 12px;
    margin-right: 10px;
}

.task-assignee span {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
}

.task-due-date {
    text-align: right;
}

.task-due-date small {
    font-size: 12px;
    color: #6c757d;
    line-height: 1.4;
}

/* Calendar Grid Styles */
.calendar-container {
    padding: 20px;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.calendar-day {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
}

.calendar-day:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.calendar-day-header {
    text-align: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}

.calendar-day-header h6 {
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.calendar-day-header small {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 500;
}

.calendar-tasks {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.calendar-task {
    display: flex;
    align-items: center;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.calendar-task:hover {
    background: #e9ecef;
}

.task-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 8px;
    flex-shrink: 0;
}

.task-info {
    flex: 1;
    min-width: 0;
}

.task-info .task-title {
    font-size: 11px;
    font-weight: 600;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.task-info .task-project {
    font-size: 10px;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.task-date-info {
    margin-top: 2px;
}

.task-date-info small {
    font-size: 9px;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    .timeline-container {
        padding: 15px;
    }

    .timeline-container::before {
        left: 25px;
    }

    .timeline-day {
        padding-left: 50px;
    }

    .timeline-day::before {
        left: 15px;
        width: 16px;
        height: 16px;
    }

    .task-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .task-card-body {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .task-due-date {
        text-align: left;
    }

    .calendar-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }

    .calendar-day {
        padding: 12px;
    }
}

@media (max-width: 576px) {
    .timeline-day-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .timeline-date h6 {
        font-size: 16px;
    }

    .timeline-date small {
        font-size: 12px;
    }

    .calendar-grid {
        grid-template-columns: 1fr;
    }

    .timeline-task-card {
        padding: 15px;
    }

    .task-title h6 {
        font-size: 14px;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Task Status Chart removed - now showing task list instead

    // Task Priority Chart removed - now showing task list instead

    // Competition period change function
    function changeCompetitionPeriod(period) {
        // Remove active class from all buttons
        document.querySelectorAll('[data-period]').forEach(btn => {
            btn.classList.remove('active');
        });

        // Add active class to clicked button
        event.target.closest('button').classList.add('active');

        // Update content based on period
        const content = document.getElementById('competitionContent');

        // You can implement AJAX calls here to fetch different period data
        // For now, we'll just show a loading state
        content.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-white-50 mt-2">Loading ${period} data...</p>
            </div>
        `;

        // Simulate loading (replace with actual AJAX call)
        setTimeout(() => {
            // Reload the page to show updated data
            window.location.reload();
        }, 1000);
    }

    // Timeline toggle function
    function toggleTimelineView() {
        const timelineView = document.getElementById('timelineView');
        const calendarView = document.getElementById('calendarView');
        const toggleButton = event.target.closest('button');

        // Check if elements exist before trying to access them
        if (!timelineView || !calendarView || !toggleButton) {
            console.warn('Timeline elements not found');
            return;
        }

        if (timelineView.style.display === 'none') {
            // Show timeline view
            timelineView.style.display = 'block';
            calendarView.style.display = 'none';
            toggleButton.innerHTML = '<i class="bx bx-grid-alt me-1"></i>Calendar View';
        } else {
            // Show calendar view
            timelineView.style.display = 'none';
            calendarView.style.display = 'block';
            toggleButton.innerHTML = '<i class="bx bx-list-ul me-1"></i>Timeline View';
        }
    }

    // Debug data
    console.log('Recent Activity Data:', @json($data['recent_activity']));
    console.log('Tasks by Status:', @json($data['tasks_by_status']));
    console.log('Tasks by Priority:', @json($data['tasks_by_priority']));
    console.log('Timeline Data:', @json($data['timeline_data']));
    console.log('Timeline Data Count:', {{ count($data['timeline_data']['sequential']) }});

    // Check if timeline elements exist on page load
    document.addEventListener('DOMContentLoaded', function() {
        const timelineView = document.getElementById('timelineView');
        const calendarView = document.getElementById('calendarView');
        console.log('Timeline View element:', timelineView);
        console.log('Calendar View element:', calendarView);

        // Add hover effects for task bars
        const taskBars = document.querySelectorAll('.timeline-task-bar');
        taskBars.forEach(bar => {
            bar.addEventListener('mouseenter', function() {
                const taskId = this.getAttribute('data-task-id');
                const detailCard = document.querySelector(`[data-task-id="${taskId}"]`);
                if (detailCard) {
                    detailCard.style.backgroundColor = '#e3f2fd';
                    detailCard.style.borderColor = '#007bff';
                }
            });

            bar.addEventListener('mouseleave', function() {
                const taskId = this.getAttribute('data-task-id');
                const detailCard = document.querySelector(`[data-task-id="${taskId}"]`);
                if (detailCard) {
                    detailCard.style.backgroundColor = '#f8f9fa';
                    detailCard.style.borderColor = '#e9ecef';
                }
            });
        });
    });
</script>
@endpush
@endsection
