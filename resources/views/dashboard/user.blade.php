@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <!-- Welcome Header -->
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-12">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Welcome back, {{ $userData['user']->name }}!</h5>
                            <p class="mb-4">Here's your personal task overview and progress summary.</p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                                    <i class="bx bx-task me-1"></i>View All Tasks
                                </a>
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
            <div class="card h-100 card-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <div class="avatar-initial rounded bg-label-{{ $userData['rankings']['overall']['user_ranking']['rank'] <= 3 ? 'success' : ($userData['rankings']['overall']['user_ranking']['rank'] <= 10 ? 'warning' : 'primary') }}">
                                    @if($userData['rankings']['overall']['user_ranking']['rank'] == 1)
                                        <i class="bx bx-trophy" style="color: #1c3644;"></i>
                                    @elseif($userData['rankings']['overall']['user_ranking']['rank'] == 2)
                                        <i class="bx bx-medal" style="color: #1c3644;"></i>
                                    @elseif($userData['rankings']['overall']['user_ranking']['rank'] == 3)
                                        <i class="bx bx-award" style="color: #1c3644;"></i>
                                    @else
                                        <i class="bx bx-user" style="color: #1c3644;"></i>
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
            <div class="card h-100 card-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <div class="avatar-initial rounded bg-label-{{ $userData['rankings']['monthly']['user_ranking']['rank'] <= 3 ? 'success' : ($userData['rankings']['monthly']['user_ranking']['rank'] <= 10 ? 'warning' : 'primary') }}">
                                    @if($userData['rankings']['monthly']['user_ranking']['rank'] == 1)
                                        <i class="bx bx-trophy" style="color: #1c3644;"></i>
                                    @elseif($userData['rankings']['monthly']['user_ranking']['rank'] == 2)
                                        <i class="bx bx-medal" style="color: #1c3644;"></i>
                                    @elseif($userData['rankings']['monthly']['user_ranking']['rank'] == 3)
                                        <i class="bx bx-award" style="color: #1c3644;"></i>
                                    @else
                                        <i class="bx bx-calendar" style="color: #1c3644;"></i>
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
    <div class="row mb-4">
        <!-- Tasks by Status List -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark fw-semibold">My Tasks by Status</h5>
                    <small class="text-muted">{{ $userData['tasks_by_status']->total() }} total tasks</small>
                </div>
                <div class="card-body p-0">
                    @if($userData['tasks_by_status']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-dark fw-semibold">Task</th>
                                        <th class="text-dark fw-semibold">Status</th>
                                        <th class="text-dark fw-semibold">Project</th>
                                        <th class="text-dark fw-semibold">Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userData['tasks_by_status'] as $task)
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
                                                    @if($task->folder)
                                                        <small class="text-muted">{{ $task->folder->name }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : ($task->status === 'in_review' ? 'info' : 'primary')) }} text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 600;">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                        </td>
                                        <td>
                                            @if($task->due_date)
                                                <span class="text-dark fw-semibold {{ $task->due_date < now() && $task->status !== 'completed' ? 'text-danger' : '' }}">
                                                    {{ $task->due_date->format('M j, Y') }}
                                                </span>
                                                @if($task->due_date < now() && $task->status !== 'completed')
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
                        @if($userData['tasks_by_status']->hasPages())
                            <div class="card-footer">
                                {{ $userData['tasks_by_status']->appends(request()->except('status_page'))->links() }}
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
                    <h5 class="card-title mb-0 text-dark fw-semibold">My Tasks by Priority</h5>
                    <small class="text-muted">{{ $userData['tasks_by_priority']->total() }} total tasks</small>
                </div>
                <div class="card-body p-0">
                    @if($userData['tasks_by_priority']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-dark fw-semibold">Task</th>
                                        <th class="text-dark fw-semibold">Priority</th>
                                        <th class="text-dark fw-semibold">Project</th>
                                        <th class="text-dark fw-semibold">Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($userData['tasks_by_priority'] as $task)
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
                                                    @if($task->folder)
                                                        <small class="text-muted">{{ $task->folder->name }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'success')) }} text-white px-2 py-1 rounded-pill" style="font-size: 11px; font-weight: 600;">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                        </td>
                                        <td>
                                            @if($task->due_date)
                                                <span class="text-dark fw-semibold {{ $task->due_date < now() && $task->status !== 'completed' ? 'text-danger' : '' }}">
                                                    {{ $task->due_date->format('M j, Y') }}
                                                </span>
                                                @if($task->due_date < now() && $task->status !== 'completed')
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
                        @if($userData['tasks_by_priority']->hasPages())
                            <div class="card-footer">
                                {{ $userData['tasks_by_priority']->appends(request()->except('priority_page'))->links() }}
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
        <!-- Performance Metrics -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark fw-semibold">Performance Metrics</h5>
                    <span class="badge bg-label-primary">
                        <i class="bx bx-trending-up"></i> This Period
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Avg Completion Time -->
                        <div class="col-6">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded position-relative overflow-hidden">
                                <div class="position-absolute top-0 end-0 p-2 opacity-25">
                                    <i class="bx bx-time-five" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="text-primary mb-1 fw-bold">{{ $userData['performance']['avg_completion_time'] }}</h4>
                                <small class="text-muted d-block">Avg. Days to Complete</small>
                                @if($userData['performance']['avg_completion_time'] < 5)
                                    <small class="badge bg-success mt-1">Excellent</small>
                                @elseif($userData['performance']['avg_completion_time'] < 10)
                                    <small class="badge bg-info mt-1">Good</small>
                                @else
                                    <small class="badge bg-warning mt-1">Can Improve</small>
                                @endif
                            </div>
                        </div>

                        <!-- Completed This Week -->
                        <div class="col-6">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded position-relative overflow-hidden">
                                <div class="position-absolute top-0 end-0 p-2 opacity-25">
                                    <i class="bx bx-check-double" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="text-success mb-1 fw-bold">{{ $userData['performance']['completed_this_week'] }}</h4>
                                <small class="text-muted d-block">Completed This Week</small>
                                <small class="text-muted">of {{ $userData['performance']['tasks_this_week'] }} tasks</small>
                            </div>
                        </div>

                        <!-- Tasks This Month -->
                        <div class="col-6">
                            <div class="text-center p-3 bg-info bg-opacity-10 rounded position-relative overflow-hidden">
                                <div class="position-absolute top-0 end-0 p-2 opacity-25">
                                    <i class="bx bx-calendar" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="text-info mb-1 fw-bold">{{ $userData['performance']['tasks_this_month'] }}</h4>
                                <small class="text-muted d-block">Tasks This Month</small>
                                <small class="text-muted">Total assigned</small>
                            </div>
                        </div>

                        <!-- Completed This Month -->
                        <div class="col-6">
                            <div class="text-center p-3 bg-warning bg-opacity-10 rounded position-relative overflow-hidden">
                                <div class="position-absolute top-0 end-0 p-2 opacity-25">
                                    <i class="bx bx-trophy" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="text-warning mb-1 fw-bold">{{ $userData['performance']['completed_this_month'] }}</h4>
                                <small class="text-muted d-block">Completed This Month</small>
                                @php
                                    $monthlyRate = $userData['performance']['tasks_this_month'] > 0
                                        ? round(($userData['performance']['completed_this_month'] / $userData['performance']['tasks_this_month']) * 100)
                                        : 0;
                                @endphp
                                <small class="text-muted">{{ $monthlyRate }}% rate</small>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Summary Bar -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted fw-semibold">Overall Performance</small>
                            <small class="text-primary fw-semibold">{{ $userData['rankings']['overall']['user_ranking']['performance_score'] }}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-gradient-primary"
                                 role="progressbar"
                                 style="width: {{ $userData['rankings']['overall']['user_ranking']['performance_score'] }}%"
                                 aria-valuenow="{{ $userData['rankings']['overall']['user_ranking']['performance_score'] }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
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
                                <div class="me-2 flex-grow-1">
                                    <h6 class="mb-0">{{ $task->title }}</h6>
                                    <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                    <div class="mt-1">
                                        <x-task-progress :task="$task" :showLabel="false" :showPercentage="true" size="sm" />
                                    </div>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark fw-semibold">Upcoming Due Dates</h5>
                    <span class="badge bg-warning text-white">{{ $userData['upcoming_tasks_paginated']->total() }} tasks</span>
                </div>
                <div class="card-body">
                    @forelse($userData['upcoming_tasks_paginated'] as $task)
                        <div class="d-flex align-items-center mb-3 p-2 bg-warning bg-opacity-10 rounded position-relative"
                             style="cursor: pointer; transition: all 0.2s;"
                             onclick="window.location.href='{{ route('tasks.show', $task->id) }}'"
                             onmouseover="this.style.transform='translateX(5px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'"
                             onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-time"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2 flex-grow-1">
                                    <h6 class="mb-0 text-dark fw-semibold">{{ Str::limit($task->title, 35) }}</h6>
                                    <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                    <div class="mt-1">
                                        <x-task-progress :task="$task" :showLabel="false" :showPercentage="true" size="sm" />
                                    </div>
                                </div>
                                <div class="user-progress text-end">
                                    <small class="text-warning fw-semibold d-block">{{ $task->due_date->format('M d') }}</small>
                                    <small class="text-muted">{{ $task->due_date->diffForHumans() }}</small>
                                    <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'info') }} mt-1">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bx bx-calendar-check fs-1 text-muted opacity-50"></i>
                            <p class="text-muted mt-2">No upcoming due dates</p>
                        </div>
                    @endforelse

                    @if($userData['upcoming_tasks_paginated']->hasPages())
                        <div class="mt-3 pt-3 border-top">
                            {{ $userData['upcoming_tasks_paginated']->appends(request()->except('upcoming_page'))->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="col-lg-6 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark fw-semibold">Overdue Tasks</h5>
                    <span class="badge bg-danger text-white">{{ $userData['overdue_tasks']->total() }} tasks</span>
                </div>
                <div class="card-body">
                    @forelse($userData['overdue_tasks'] as $task)
                        <div class="d-flex align-items-center mb-3 p-2 bg-danger bg-opacity-10 rounded position-relative border-start border-danger border-3"
                             style="cursor: pointer; transition: all 0.2s;"
                             onclick="window.location.href='{{ route('tasks.show', $task->id) }}'"
                             onmouseover="this.style.transform='translateX(5px)'; this.style.boxShadow='0 2px 8px rgba(220,53,69,0.2)'"
                             onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="bx bx-error-circle"></i>
                                </span>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2 flex-grow-1">
                                    <h6 class="mb-0 text-dark fw-semibold">{{ Str::limit($task->title, 35) }}</h6>
                                    <small class="text-muted">{{ $task->project->name ?? 'No Project' }}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="user-progress text-end">
                                    <small class="text-danger fw-semibold d-block">{{ $task->due_date->format('M d, Y') }}</small>
                                    <small class="text-danger">{{ $task->due_date->diffForHumans() }}</small>
                                    <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'info') }} mt-1">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bx bx-check-circle fs-1 text-success opacity-50"></i>
                            <p class="text-muted mt-2">No overdue tasks - Great work!</p>
                        </div>
                    @endforelse

                    @if($userData['overdue_tasks']->hasPages())
                        <div class="mt-3 pt-3 border-top">
                            {{ $userData['overdue_tasks']->appends(request()->except('overdue_page'))->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tasks and Notifications -->
    <div class="row">
        <!-- Recent Tasks -->
        <div class="col-lg-12 col-md-12 col-12 mb-4">
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
                                    <th>Start Date</th>
                                    <th>Due Date</th>
                                    <th>Duration</th>
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
                                            @if($task->start_date)
                                                <small class="text-info">{{ $task->start_date->format('M d, Y') }}</small>
                                            @else
                                                <small class="text-muted">No start date</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->due_date)
                                                <small class="text-muted">{{ $task->due_date->format('M d, Y') }}</small>
                                            @else
                                                <small class="text-muted">No due date</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->start_date && $task->due_date)
                                                @php
                                                    $duration = $task->start_date->diffInDays($task->due_date);
                                                @endphp
                                                <small class="text-primary fw-semibold">
                                                    {{ $duration }} {{ $duration == 1 ? 'day' : 'days' }}
                                                </small>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No tasks found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
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
