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

    <!-- TimelineJS Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-timeline me-2 text-primary"></i>Task Timeline
                    </h5>
                    <p class="text-muted mb-0">Interactive timeline showing tasks for the next 20 days</p>
                </div>
                <div class="card-body">
                    <!-- Immediate fallback content that shows without JavaScript -->
                    <div id="timeline-fallback" style="display: block;">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="text-muted">Loading Timeline...</h5>
                            <p class="text-muted">Fetching task data from database...</p>
                            <div class="mt-3">
                                <small class="text-muted">If this takes too long, <a href="#" onclick="showFallbackContent(); return false;" class="text-primary">click here</a> to show sample data.</small>
                            </div>
                        </div>
                    </div>

                    <!-- TimelineJS container -->
                    <div id="timeline-embed" style="width: 100%; height: 600px; display: none;"></div>

                    <!-- Error fallback -->
                    <div id="timeline-error" style="display: none;">
                        <div class="alert alert-warning">
                            <h5><i class="bx bx-error-circle me-2"></i>Timeline Loading Issue</h5>
                            <p>There was a problem loading the timeline. This could be due to:</p>
                            <ul>
                                <li>Database connection issues</li>
                                <li>No tasks with dates in the next 20 days</li>
                                <li>JavaScript loading problems</li>
                            </ul>
                            <button class="btn btn-primary btn-sm" onclick="retryTimeline()">Retry Loading</button>
                            <button class="btn btn-outline-secondary btn-sm ms-2" onclick="showFallbackContent()">Show Sample Data</button>
                        </div>
                    </div>

                    <!-- Sample data fallback -->
                    <div id="timeline-sample" style="display: none;">
                        <div class="alert alert-info">
                            <h5><i class="bx bx-info-circle me-2"></i>Sample Task Timeline</h5>
                            <p>Showing sample tasks for demonstration. Connect to database to see real data.</p>
                        </div>

                        <!-- Simple timeline using Bootstrap cards -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Tomorrow</h6>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title">Website Design Project</h6>
                                        <p class="card-text small">Create responsive website design for new client project</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-warning text-dark">In Progress</span>
                                            <span class="badge bg-danger">High Priority</span>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Assigned to: John Doe</small>
                                        </div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-warning" style="width: 45%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>In 3 Days</h6>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title">Content Review</h6>
                                        <p class="card-text small">Review and approve content for upcoming marketing campaign</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-info">Assigned</span>
                                            <span class="badge bg-warning text-dark">Medium Priority</span>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Assigned to: Jane Smith</small>
                                        </div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-info" style="width: 15%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>In 7 Days</h6>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title">Database Migration</h6>
                                        <p class="card-text small">Migrate user data to new database structure</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-success">Completed</span>
                                            <span class="badge bg-danger">Critical Priority</span>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Assigned to: Mike Johnson</small>
                                        </div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button class="btn btn-primary btn-sm" onclick="retryTimeline()">Try Interactive Timeline</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('styles')
<script>
// Global functions for timeline management - available immediately
console.log('Timeline functions loaded');

function showFallbackContent() {
    console.log('showFallbackContent called');
    try {
        document.getElementById('timeline-fallback').style.display = 'none';
        document.getElementById('timeline-embed').style.display = 'none';
        document.getElementById('timeline-error').style.display = 'none';
        document.getElementById('timeline-sample').style.display = 'block';
        console.log('Fallback content shown successfully');
    } catch (error) {
        console.error('Error showing fallback content:', error);
    }
}

function retryTimeline() {
    console.log('retryTimeline called');
    location.reload();
}

function showError(message) {
    console.log('showError called:', message);
    document.getElementById('timeline-fallback').style.display = 'none';
    document.getElementById('timeline-embed').style.display = 'none';
    document.getElementById('timeline-sample').style.display = 'none';
    document.getElementById('timeline-error').style.display = 'block';
    document.getElementById('timeline-error').innerHTML = `
        <div class="alert alert-warning">
            <h5><i class="bx bx-error-circle me-2"></i>Timeline Loading Issue</h5>
            <p>${message}</p>
            <button class="btn btn-primary btn-sm" onclick="retryTimeline()">Retry Loading</button>
            <button class="btn btn-outline-secondary btn-sm ms-2" onclick="showFallbackContent()">Show Sample Data</button>
        </div>
    `;
}

function showTimeline() {
    console.log('showTimeline called');
    document.getElementById('timeline-fallback').style.display = 'none';
    document.getElementById('timeline-error').style.display = 'none';
    document.getElementById('timeline-sample').style.display = 'none';
    document.getElementById('timeline-embed').style.display = 'block';
}
</script>
<style>
/* Manager Dashboard Styles */

/* TimelineJS Custom Styling */
.tl-timeline {
    font-family: 'Inter', sans-serif !important;
}

.tl-timeline .tl-slide {
    background: #ffffff !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
}

.tl-timeline .tl-slide-content {
    padding: 20px !important;
}

.tl-timeline .tl-headline {
    color: #2c3e50 !important;
    font-weight: 600 !important;
}

.tl-timeline .tl-text {
    color: #6c757d !important;
    line-height: 1.6 !important;
}

/* Timeline Task Details Styling */
.timeline-task-details {
    font-family: 'Inter', sans-serif;
}

.timeline-task-details .task-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.timeline-task-details .badge {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.timeline-task-details .task-info p {
    margin-bottom: 8px;
    font-size: 14px;
    line-height: 1.4;
}

.timeline-task-details .task-info p:last-child {
    margin-bottom: 0;
}

.timeline-task-details .btn {
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 4px;
}

/* No Tasks State Styling */
.timeline-no-tasks {
    text-align: center;
    padding: 20px;
}

.timeline-no-tasks .no-tasks-icon {
    margin-bottom: 20px;
}

.timeline-no-tasks h5 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 600;
}

.timeline-no-tasks p {
    color: #6c757d;
    margin-bottom: 20px;
    line-height: 1.5;
}

.timeline-no-tasks .no-tasks-suggestions {
    text-align: left;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.timeline-no-tasks .no-tasks-suggestions h6 {
    color: #495057;
    margin-bottom: 10px;
    font-weight: 600;
}

.timeline-no-tasks .no-tasks-suggestions ul {
    color: #6c757d;
    padding-left: 20px;
    margin-bottom: 0;
}

.timeline-no-tasks .no-tasks-suggestions li {
    margin-bottom: 5px;
    line-height: 1.4;
}

/* Database Error State Styling */
.timeline-db-error {
    text-align: center;
    padding: 20px;
}

.timeline-db-error .db-error-icon {
    margin-bottom: 20px;
}

.timeline-db-error h5 {
    color: #dc3545;
    margin-bottom: 15px;
    font-weight: 600;
}

.timeline-db-error p {
    color: #6c757d;
    margin-bottom: 20px;
    line-height: 1.5;
}

.timeline-db-error .db-error-details {
    text-align: left;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.timeline-db-error .db-error-details h6 {
    color: #495057;
    margin-bottom: 10px;
    font-weight: 600;
}

.timeline-db-error .db-error-solutions {
    text-align: left;
    background: #fff3cd;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.timeline-db-error .db-error-solutions h6 {
    color: #495057;
    margin-bottom: 10px;
    font-weight: 600;
}

.timeline-db-error .db-error-solutions ul {
    color: #6c757d;
    padding-left: 20px;
    margin-bottom: 0;
}

.timeline-db-error .db-error-solutions li {
    margin-bottom: 5px;
    line-height: 1.4;
}

.tl-timeline .tl-timenav {
    background: #f8f9fa !important;
    border-radius: 8px !important;
}

.tl-timeline .tl-timenav-slider {
    background: #007bff !important;
}

.tl-timeline .tl-timenav-marker {
    background: #007bff !important;
    border: 2px solid #ffffff !important;
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
<!-- TimelineJS CDN -->
<link title="timeline-styles" rel="stylesheet" href="https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css">
<script src="https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js"></script>
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

    // Timeline functions are now defined globally in the styles section

    // Initialize TimelineJS with timeout protection
    document.addEventListener('DOMContentLoaded', function() {
        console.log('TimelineJS Debug: Starting initialization...');

        // Set a timeout to show fallback content if loading takes too long
        const loadingTimeout = setTimeout(function() {
            console.warn('TimelineJS Debug: Loading timeout reached, showing fallback content');
            showFallbackContent();
        }, 5000); // 5 second timeout - reduced for better UX

        // Try to initialize TimelineJS
        setTimeout(function() {
            try {
                // Check if TimelineJS is loaded
                if (typeof TL === 'undefined') {
                    console.error('TimelineJS Debug: TL library not loaded');
                    clearTimeout(loadingTimeout);
                    showError('TimelineJS library failed to load. Please refresh the page.');
                    return;
                }

                // Simple sample data for TimelineJS
                const timelineData = {
                    "title": {
                        "media": {
                            "url": "{{ asset('DAssets/assets/img/icons/unicons/chart-success.png') }}",
                            "caption": "Task Management Timeline"
                        },
                        "text": {
                            "headline": "Manager Task Timeline",
                            "text": "Interactive timeline showing all team tasks for the next 20 days"
                        }
                    },
                    "events": [
                        {
                            "media": {
                                "url": "{{ asset('DAssets/assets/img/icons/unicons/chart.png') }}",
                                "caption": "Sample Task 1"
                            },
                            "start_date": {
                                "year": {{ now()->addDay()->year }},
                                "month": {{ now()->addDay()->month }},
                                "day": {{ now()->addDay()->day }}
                            },
                            "text": {
                                "headline": "Website Design Project",
                                "text": "<div class='timeline-task-details'>
                                    <div class='task-meta mb-2'>
                                        <span class='badge badge-sm' style='background-color: #ffc107; color: white; margin-right: 8px;'>In Progress</span>
                                        <span class='badge badge-sm' style='background-color: #fd7e14; color: white;'>High Priority</span>
                                    </div>
                                    <div class='task-info'>
                                        <p><strong>Project:</strong> Client Website</p>
                                        <p><strong>Assigned to:</strong> John Doe</p>
                                        <p><strong>Description:</strong> Create responsive website design for new client project</p>
                                        <p><strong>Progress:</strong> 45%</p>
                                    </div>
                                </div>"
                            },
                            "background": {
                                "color": "#ffc107"
                            }
                        },
                        {
                            "media": {
                                "url": "{{ asset('DAssets/assets/img/icons/unicons/chart.png') }}",
                                "caption": "Sample Task 2"
                            },
                            "start_date": {
                                "year": {{ now()->addDays(3)->year }},
                                "month": {{ now()->addDays(3)->month }},
                                "day": {{ now()->addDays(3)->day }}
                            },
                            "text": {
                                "headline": "Content Review",
                                "text": "<div class='timeline-task-details'>
                                    <div class='task-meta mb-2'>
                                        <span class='badge badge-sm' style='background-color: #17a2b8; color: white; margin-right: 8px;'>Assigned</span>
                                        <span class='badge badge-sm' style='background-color: #ffc107; color: white;'>Medium Priority</span>
                                    </div>
                                    <div class='task-info'>
                                        <p><strong>Project:</strong> Marketing Campaign</p>
                                        <p><strong>Assigned to:</strong> Jane Smith</p>
                                        <p><strong>Description:</strong> Review and approve content for upcoming marketing campaign</p>
                                        <p><strong>Progress:</strong> 15%</p>
                                    </div>
                                </div>"
                            },
                            "background": {
                                "color": "#17a2b8"
                            }
                        },
                        {
                            "media": {
                                "url": "{{ asset('DAssets/assets/img/icons/unicons/chart.png') }}",
                                "caption": "Sample Task 3"
                            },
                            "start_date": {
                                "year": {{ now()->addDays(7)->year }},
                                "month": {{ now()->addDays(7)->month }},
                                "day": {{ now()->addDays(7)->day }}
                            },
                            "text": {
                                "headline": "Database Migration",
                                "text": "<div class='timeline-task-details'>
                                    <div class='task-meta mb-2'>
                                        <span class='badge badge-sm' style='background-color: #28a745; color: white; margin-right: 8px;'>Completed</span>
                                        <span class='badge badge-sm' style='background-color: #dc3545; color: white;'>Critical Priority</span>
                                    </div>
                                    <div class='task-info'>
                                        <p><strong>Project:</strong> System Upgrade</p>
                                        <p><strong>Assigned to:</strong> Mike Johnson</p>
                                        <p><strong>Description:</strong> Migrate user data to new database structure</p>
                                        <p><strong>Progress:</strong> 100%</p>
                                    </div>
                                </div>"
                            },
                            "background": {
                                "color": "#28a745"
                            }
                        }
                    ]
                };

                console.log('TimelineJS Debug: Timeline data prepared:', timelineData);

                // Initialize TimelineJS
                window.timeline = new TL.Timeline('timeline-embed', timelineData, {
                    width: '100%',
                    height: '600px',
                    font: 'default',
                    scale_factor: 1,
                    timenav_height: 150,
                    timenav_height_percentage: 25,
                    timenav_mobile_height_percentage: 40,
                    timenav_position: 'bottom',
                    optimal_tick_width: 100,
                    base_class: 'tl-timeline',
                    timenav_height_min: 100,
                    marker_height_min: 30,
                    marker_width_min: 100,
                    marker_padding: 5,
                    start_at_slide: 0,
                    start_at_end: false,
                    menubar_height: 0,
                    skin: 'default',
                    duration: 1000,
                    ease: 'easeInOut',
                    dragging: true,
                    trackResize: true,
                    slide_padding_lr: 100,
                    slide_default_fade: '0%',
                    language: 'en',
                    ga_property_id: '',
                    debug: false
                });

                console.log('TimelineJS Debug: Timeline initialized successfully');
                clearTimeout(loadingTimeout);
                showTimeline();

                // Add event listeners
                window.timeline.on('ready', function() {
                    console.log('TimelineJS Debug: Timeline is ready');
                });

                window.timeline.on('change', function(e) {
                    console.log('TimelineJS Debug: Timeline slide changed to:', e.data);
                });

            } catch (error) {
                console.error('TimelineJS Debug: Error initializing timeline:', error);
                clearTimeout(loadingTimeout);
                showError('Failed to initialize timeline: ' + error.message);
            }
        }, 2000); // 2 second delay to ensure everything is loaded
    });
</script>
@endpush
@endsection
