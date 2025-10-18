@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Detailed Project Progress Report</h4>
            <p class="text-muted">Comprehensive project analysis with task tracking, team performance, and timeline insights</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.projects') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to Projects
            </a>
            <button class="btn btn-outline-primary" onclick="refreshReport()">
                <i class="bx bx-refresh me-1"></i>Refresh
            </button>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-download me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'project-progress')">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'project-progress')">Export as Excel</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bx bx-filter-alt me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.projects.progress') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="project_id" class="form-label">Specific Project</label>
                        <select name="project_id" id="project_id" class="form-select">
                            <option value="">All Projects</option>
                            @foreach($allProjects as $proj)
                                <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                    {{ $proj->short_code ?? $proj->id }} - {{ $proj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status[]" id="status" class="form-select" multiple>
                            <option value="active" {{ in_array('active', $filters['status'] ?? []) ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ in_array('completed', $filters['status'] ?? []) ? 'selected' : '' }}>Completed</option>
                            <option value="on_hold" {{ in_array('on_hold', $filters['status'] ?? []) ? 'selected' : '' }}>On Hold</option>
                            <option value="pending" {{ in_array('pending', $filters['status'] ?? []) ? 'selected' : '' }}>Pending</option>
                        </select>
                        <small class="text-muted">Hold Ctrl to select multiple</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_from" class="form-label">Created From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_to" class="form-label">Created To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-search me-1"></i>Apply Filters
                    </button>
                    <a href="{{ route('reports.projects.progress') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Project Progress Details -->
    <div class="row">
        @if($projects->count() > 0)
            @foreach($projects as $project)
                <div class="col-12 mb-4">
                    <div class="card project-report-card">
                        <!-- Project Header -->
                        <div class="card-header d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-start flex-grow-1">
                                <div class="avatar avatar-lg me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="bx bx-folder-open fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="mb-1">{{ $project['name'] }}</h4>
                                    <div class="d-flex flex-wrap gap-3 align-items-center">
                                        <span class="text-muted"><i class="bx bx-code-alt me-1"></i>{{ $project['short_code'] }}</span>
                                        <span class="badge bg-{{ $project['status'] === 'active' ? 'success' : ($project['status'] === 'completed' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($project['status']) }}
                                        </span>
                                        @if($project['is_at_risk'])
                                            <span class="badge bg-danger"><i class="bx bx-error-circle me-1"></i>At Risk</span>
                                        @endif
                                        <span class="text-muted"><i class="bx bx-user me-1"></i>Owner: {{ $project['owner'] }}</span>
                                    </div>
                                    @if($project['description'])
                                        <p class="mt-2 mb-0 text-muted">{{ Str::limit($project['description'], 150) }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="progress-circle mb-2">
                                    <div class="display-6 fw-bold text-primary">{{ $project['completion_percentage'] }}%</div>
                                    <small class="text-muted">Complete</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Quick Stats -->
                            <div class="row mb-4">
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="stat-card bg-primary-subtle p-3 rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                                <h3 class="mb-0 text-primary">{{ $project['total_tasks'] }}</h3>
                                            <small class="text-muted">Total Tasks</small>
                                            </div>
                                            <div class="avatar bg-primary">
                                                <i class="bx bx-task fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="stat-card bg-success-subtle p-3 rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                                <h3 class="mb-0 text-success">{{ $project['completed_tasks'] }}</h3>
                                            <small class="text-muted">Completed</small>
                                            </div>
                                            <div class="avatar bg-success">
                                                <i class="bx bx-check-circle fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="stat-card bg-warning-subtle p-3 rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h3 class="mb-0 text-warning">{{ $project['in_progress_tasks'] }}</h3>
                                                <small class="text-muted">In Progress</small>
                                            </div>
                                            <div class="avatar bg-warning">
                                                <i class="bx bx-loader-circle fs-4"></i>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="stat-card bg-danger-subtle p-3 rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                                <h3 class="mb-0 text-danger">{{ $project['overdue_tasks'] }}</h3>
                                            <small class="text-muted">Overdue</small>
                                            </div>
                                            <div class="avatar bg-danger">
                                                <i class="bx bx-error-circle fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Breakdown Tabs -->
                            <ul class="nav nav-tabs mb-3" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview-{{ $project['id'] }}">
                                        <i class="bx bx-pie-chart-alt-2 me-1"></i>Overview
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tasks-{{ $project['id'] }}">
                                        <i class="bx bx-list-ul me-1"></i>Tasks Details
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#team-{{ $project['id'] }}">
                                        <i class="bx bx-group me-1"></i>Team Performance
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#timeline-{{ $project['id'] }}">
                                        <i class="bx bx-time-five me-1"></i>Timeline
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Overview Tab -->
                                <div class="tab-pane fade show active" id="overview-{{ $project['id'] }}">
                                    <div class="row">
                                        <!-- Task Status Breakdown -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="mb-3"><i class="bx bx-bar-chart me-2"></i>Task Status Breakdown</h6>
                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">Completed</span>
                                                            <span class="fw-semibold">{{ $project['completed_tasks'] }} ({{ $project['completion_percentage'] }}%)</span>
                                                        </div>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar bg-success" style="width: {{ $project['completion_percentage'] }}%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">In Progress</span>
                                                            <span class="fw-semibold">{{ $project['in_progress_tasks'] }} ({{ $project['total_tasks'] > 0 ? round(($project['in_progress_tasks'] / $project['total_tasks']) * 100, 1) : 0 }}%)</span>
                                                        </div>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar bg-warning" style="width: {{ $project['total_tasks'] > 0 ? ($project['in_progress_tasks'] / $project['total_tasks']) * 100 : 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">Pending</span>
                                                            <span class="fw-semibold">{{ $project['pending_tasks'] }} ({{ $project['total_tasks'] > 0 ? round(($project['pending_tasks'] / $project['total_tasks']) * 100, 1) : 0 }}%)</span>
                                                        </div>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar bg-info" style="width: {{ $project['total_tasks'] > 0 ? ($project['pending_tasks'] / $project['total_tasks']) * 100 : 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                    @if($project['on_hold_tasks'] > 0)
                                                        <div class="mb-2">
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span class="text-muted">On Hold</span>
                                                                <span class="fw-semibold">{{ $project['on_hold_tasks'] }} ({{ $project['total_tasks'] > 0 ? round(($project['on_hold_tasks'] / $project['total_tasks']) * 100, 1) : 0 }}%)</span>
                                                            </div>
                                                            <div class="progress mb-2" style="height: 8px;">
                                                                <div class="progress-bar bg-secondary" style="width: {{ $project['total_tasks'] > 0 ? ($project['on_hold_tasks'] / $project['total_tasks']) * 100 : 0 }}%"></div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Priority Distribution -->
                                        <div class="col-md-6 mb-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="mb-3"><i class="bx bx-flag me-2"></i>Priority Distribution</h6>
                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-danger"><i class="bx bx-up-arrow-alt"></i> High Priority</span>
                                                            <span class="fw-semibold">{{ $project['high_priority_tasks'] }}</span>
                                                        </div>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar bg-danger" style="width: {{ $project['total_tasks'] > 0 ? ($project['high_priority_tasks'] / $project['total_tasks']) * 100 : 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-warning"><i class="bx bx-right-arrow-alt"></i> Medium Priority</span>
                                                            <span class="fw-semibold">{{ $project['medium_priority_tasks'] }}</span>
                                                        </div>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar bg-warning" style="width: {{ $project['total_tasks'] > 0 ? ($project['medium_priority_tasks'] / $project['total_tasks']) * 100 : 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-info"><i class="bx bx-down-arrow-alt"></i> Low Priority</span>
                                                            <span class="fw-semibold">{{ $project['low_priority_tasks'] }}</span>
                                                        </div>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar bg-info" style="width: {{ $project['total_tasks'] > 0 ? ($project['low_priority_tasks'] / $project['total_tasks']) * 100 : 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Key Metrics -->
                                        <div class="col-12">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="mb-3"><i class="bx bx-target-lock me-2"></i>Performance Metrics</h6>
                            <div class="row">
                                                        <div class="col-md-3 col-6 mb-3">
                                                            <div class="text-center p-3 border rounded bg-white">
                                                                <h4 class="text-primary mb-1">{{ $project['on_time_completion_rate'] }}%</h4>
                                                                <small class="text-muted">On-Time Completion Rate</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-6 mb-3">
                                                            <div class="text-center p-3 border rounded bg-white">
                                                                <h4 class="text-{{ $project['overdue_percentage'] > 20 ? 'danger' : 'warning' }} mb-1">{{ $project['overdue_percentage'] }}%</h4>
                                                                <small class="text-muted">Overdue Rate</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-6 mb-3">
                                                            <div class="text-center p-3 border rounded bg-white">
                                                                <h4 class="text-success mb-1">{{ $project['team_size'] }}</h4>
                                                                <small class="text-muted">Team Members</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 col-6 mb-3">
                                                            <div class="text-center p-3 border rounded bg-white">
                                                                <h4 class="text-info mb-1">{{ $project['sub_folders_count'] }}</h4>
                                                                <small class="text-muted">Sub Folders</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tasks Details Tab -->
                                <div class="tab-pane fade" id="tasks-{{ $project['id'] }}">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0"><i class="bx bx-list-check me-2"></i>Project Tasks Overview</h6>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" onclick="exportProjectTasks({{ $project['id'] }})">
                                                <i class="bx bx-download me-1"></i>Export Tasks
                                            </button>
                                            <a href="{{ route('projects.show', $project['id']) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bx bx-link-external me-1"></i>View All Tasks
                                            </a>
                                        </div>
                                    </div>

                                    @if(count($project['recent_tasks']) > 0)
                                        <!-- Task Summary Cards -->
                                        <div class="row mb-4">
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h4 class="text-primary mb-1">{{ count($project['recent_tasks']) }}</h4>
                                                        <small class="text-muted">Total Tasks</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h4 class="text-success mb-1">{{ $project['recent_tasks']->where('status', 'completed')->count() }}</h4>
                                                        <small class="text-muted">Completed</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h4 class="text-warning mb-1">{{ $project['recent_tasks']->whereIn('status', ['in_progress', 'workingon'])->count() }}</h4>
                                                        <small class="text-muted">In Progress</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h4 class="text-danger mb-1">{{ $project['recent_tasks']->where('is_overdue', true)->count() }}</h4>
                                                        <small class="text-muted">Overdue</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Task Name</th>
                                                        <th>Status</th>
                                                        <th>Priority</th>
                                                        <th>Assignee</th>
                                                        <th>Due Date</th>
                                                        <th>Completed</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($project['recent_tasks'] as $task)
                                                        <tr class="{{ $task['is_overdue'] ? 'table-warning' : '' }}">
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        @if($task['status'] === 'completed')
                                                                            <i class="bx bx-check-circle text-success"></i>
                                                                        @elseif($task['status'] === 'in_progress' || $task['status'] === 'workingon')
                                                                            <i class="bx bx-time-five text-warning"></i>
                                                                        @else
                                                                            <i class="bx bx-circle text-muted"></i>
                                                                        @endif
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold">{{ $task['name'] }}</div>
                                                                        @if($task['is_overdue'])
                                                                            <small class="text-danger">
                                                                                <i class="bx bx-time me-1"></i>Overdue
                                                                            </small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $task['status'] === 'completed' ? 'success' : ($task['status'] === 'in_progress' || $task['status'] === 'workingon' ? 'warning' : ($task['status'] === 'assigned' ? 'info' : 'secondary')) }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                                                </span>
                                                                <div class="mt-1">
                                                                    <x-task-progress :task="(object)$task" :showLabel="false" :showPercentage="true" size="sm" />
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'info') }}">
                                                                    {{ ucfirst($task['priority']) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar avatar-xs me-2">
                                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                                            {{ substr($task['assignee'], 0, 1) }}
                                                                        </span>
                                                                    </div>
                                                                    {{ $task['assignee'] }}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @if($task['due_date'])
                                                                    <span class="{{ $task['is_overdue'] ? 'text-danger fw-semibold' : 'text-muted' }}">
                                                                        {{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">No due date</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($task['completed_at'])
                                                                    <span class="text-success">
                                                                        {{ \Carbon\Carbon::parse($task['completed_at'])->format('M d, Y') }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="{{ route('tasks.show', $task['id']) }}" class="btn btn-outline-primary" title="View Task">
                                                                        <i class="bx bx-show"></i>
                                                                    </a>
                                                                    @if(auth()->user()->isManager() || auth()->user()->isSubAdmin())
                                                                        <a href="{{ route('tasks.edit', $task['id']) }}" class="btn btn-outline-secondary" title="Edit Task">
                                                                            <i class="bx bx-edit"></i>
                                                                        </a>
                                                                    @endif
                                                                    @if($task['is_overdue'])
                                                                        <button class="btn btn-outline-warning" onclick="sendTaskReminder({{ $task['id'] }})" title="Send Reminder">
                                                                            <i class="bx bx-bell"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="alert alert-info mt-3">
                                            <i class="bx bx-info-circle me-2"></i>
                                            Showing last 10 recently updated tasks. Use filters above to narrow down results.
                                            <a href="{{ route('projects.show', $project['id']) }}" class="alert-link">View all tasks</a>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="bx bx-task fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">No tasks found for this project</p>
                                            @if(auth()->user()->isManager() || auth()->user()->isSubAdmin())
                                                <a href="{{ route('tasks.create') }}?project_id={{ $project['id'] }}" class="btn btn-primary">
                                                    <i class="bx bx-plus me-1"></i>Create First Task
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Team Performance Tab -->
                                <div class="tab-pane fade" id="team-{{ $project['id'] }}">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0"><i class="bx bx-group me-2"></i>Team Performance Analytics</h6>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" onclick="exportTeamPerformance({{ $project['id'] }})">
                                                <i class="bx bx-download me-1"></i>Export
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="refreshTeamData({{ $project['id'] }})">
                                                <i class="bx bx-refresh me-1"></i>Refresh
                                            </button>
                                        </div>
                                    </div>

                                    @if(count($project['team_performance']) > 0)
                                        <!-- Team Summary Cards -->
                                        <div class="row mb-4">
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h4 class="text-primary mb-1">{{ count($project['team_performance']) }}</h4>
                                                        <small class="text-muted">Active Team Members</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h4 class="text-success mb-1">{{ round($project['team_performance']->avg('completion_rate'), 1) }}%</h4>
                                                        <small class="text-muted">Avg Completion Rate</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h4 class="text-warning mb-1">{{ $project['team_performance']->sum('pending_tasks') }}</h4>
                                                        <small class="text-muted">Total Pending Tasks</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h4 class="text-danger mb-1">{{ $project['team_performance']->sum('overdue_tasks') }}</h4>
                                                        <small class="text-muted">Overdue Tasks</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Team Member</th>
                                                        <th>Total Tasks</th>
                                                        <th>Completed</th>
                                                        <th>Pending</th>
                                                        <th>Overdue</th>
                                                        <th>Completion Rate</th>
                                                        <th>Avg Duration</th>
                                                        <th>Performance</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($project['team_performance'] as $index => $member)
                                                        <tr class="{{ $member['overdue_tasks'] > 0 ? 'table-warning' : '' }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar avatar-sm me-2">
                                                                        <span class="avatar-initial rounded-circle bg-{{ $member['completion_rate'] >= 80 ? 'success' : ($member['completion_rate'] >= 50 ? 'primary' : 'warning') }}">
                                                                            {{ substr($member['user_name'], 0, 1) }}
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold">{{ $member['user_name'] }}</div>
                                                                        <small class="text-muted">{{ $member['user_email'] ?? '' }}</small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><strong>{{ $member['total_tasks'] }}</strong></td>
                                                            <td><span class="text-success fw-semibold">{{ $member['completed_tasks'] }}</span></td>
                                                            <td><span class="text-warning fw-semibold">{{ $member['pending_tasks'] }}</span></td>
                                                            <td>
                                                                @if($member['overdue_tasks'] > 0)
                                                                    <span class="text-danger fw-semibold">{{ $member['overdue_tasks'] }}</span>
                                                                @else
                                                                    <span class="text-success">0</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="progress flex-grow-1" style="height: 8px; width: 100px;">
                                                                        <div class="progress-bar bg-{{ $member['completion_rate'] >= 80 ? 'success' : ($member['completion_rate'] >= 50 ? 'warning' : 'danger') }}"
                                                                             style="width: {{ $member['completion_rate'] }}%"></div>
                                                                    </div>
                                                                    <span class="ms-2 fw-semibold">{{ $member['completion_rate'] }}%</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @if(isset($member['avg_task_duration']) && $member['avg_task_duration'] > 0)
                                                                    <span class="text-muted">{{ $member['avg_task_duration'] }} days</span>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($member['completion_rate'] >= 80)
                                                                    <span class="badge bg-success">Excellent</span>
                                                                @elseif($member['completion_rate'] >= 60)
                                                                    <span class="badge bg-primary">Good</span>
                                                                @elseif($member['completion_rate'] >= 40)
                                                                    <span class="badge bg-warning">Fair</span>
                                                                @else
                                                                    <span class="badge bg-danger">Needs Attention</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <button class="btn btn-outline-primary" onclick="viewUserTasks({{ $member['user_id'] }}, {{ $project['id'] }})" title="View Tasks">
                                                                        <i class="bx bx-list-ul"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-info" onclick="viewUserPerformance({{ $member['user_id'] }})" title="Performance Details">
                                                                        <i class="bx bx-bar-chart"></i>
                                                                    </button>
                                                                    @if($member['overdue_tasks'] > 0)
                                                                        <button class="btn btn-outline-warning" onclick="sendReminder({{ $member['user_id'] }}, {{ $project['id'] }})" title="Send Reminder">
                                                                            <i class="bx bx-bell"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                            @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="bx bx-user-x fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">No team members assigned to this project</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Timeline Tab -->
                                <div class="tab-pane fade" id="timeline-{{ $project['id'] }}">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="mb-3"><i class="bx bx-calendar me-2"></i>Project Timeline</h6>
                                                    <div class="timeline-info">
                                                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                                            <span class="text-muted">Project Created</span>
                                                            <strong>{{ $project['created_at']->format('M d, Y') }}</strong>
                                                        </div>
                                                        @if($project['start_date'])
                                                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                                                <span class="text-muted">Start Date</span>
                                                                <strong>{{ \Carbon\Carbon::parse($project['start_date'])->format('M d, Y') }}</strong>
                                                            </div>
                                                        @endif
                                                        @if($project['due_date'])
                                                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                                                <span class="text-muted">Due Date</span>
                                                                <strong class="{{ $project['days_remaining'] !== null && $project['days_remaining'] < 0 ? 'text-danger' : '' }}">
                                                                    {{ \Carbon\Carbon::parse($project['due_date'])->format('M d, Y') }}
                                                                </strong>
                                                            </div>
                                                        @endif
                                                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                                            <span class="text-muted">Duration (so far)</span>
                                                            <strong>{{ $project['project_duration_days'] }} days</strong>
                                                        </div>
                                                        @if($project['days_remaining'] !== null)
                                                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                                                <span class="text-muted">Days Remaining</span>
                                                                <strong class="{{ $project['days_remaining'] < 0 ? 'text-danger' : ($project['days_remaining'] < 7 ? 'text-warning' : 'text-success') }}">
                                                                    {{ $project['days_remaining'] }} days
                                                                    @if($project['days_remaining'] < 0)
                                                                        (Overdue)
                                                                    @endif
                                                                </strong>
                                                            </div>
                                                        @endif
                                                        @if($project['estimated_completion_date'])
                                                            <div class="d-flex justify-content-between">
                                                                <span class="text-muted">Estimated Completion</span>
                                                                <strong class="{{ !$project['is_on_schedule'] ? 'text-danger' : 'text-success' }}">
                                                                    {{ $project['estimated_completion_date']->format('M d, Y') }}
                                                                    @if(!$project['is_on_schedule'])
                                                                        <i class="bx bx-error-circle text-danger"></i>
                                                                    @endif
                                                                </strong>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="mb-3"><i class="bx bx-trending-up me-2"></i>Progress Indicators</h6>

                                                    <!-- Overall Progress -->
                                                    <div class="mb-4">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Overall Completion</span>
                                                            <strong>{{ $project['completion_percentage'] }}%</strong>
                                                        </div>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-{{ $project['completion_percentage'] >= 75 ? 'success' : ($project['completion_percentage'] >= 50 ? 'primary' : 'warning') }}"
                                                                 style="width: {{ $project['completion_percentage'] }}%">
                                                                {{ $project['completion_percentage'] }}%
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Schedule Status -->
                                                    <div class="mb-3">
                                                        <h6 class="mb-2">Schedule Status</h6>
                                                        @if($project['is_on_schedule'])
                                                            <div class="alert alert-success mb-0">
                                                                <i class="bx bx-check-circle me-2"></i>
                                                                Project is on schedule
                                                            </div>
                                                        @else
                                                            <div class="alert alert-danger mb-0">
                                                                <i class="bx bx-error-circle me-2"></i>
                                                                Project is behind schedule
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Risk Assessment -->
                                                    <div>
                                                        <h6 class="mb-2">Risk Assessment</h6>
                                                        @if($project['is_at_risk'])
                                                            <div class="alert alert-warning mb-0">
                                                                <i class="bx bx-error me-2"></i>
                                                                <strong>Action Required:</strong>
                                                                <ul class="mb-0 mt-2 ps-3">
                                                                    @if($project['overdue_tasks'] > 0)
                                                                        <li>{{ $project['overdue_tasks'] }} overdue task(s) need immediate attention</li>
                                                                    @endif
                                                                    @if($project['days_remaining'] !== null && $project['days_remaining'] < 7)
                                                                        <li>Project deadline is approaching ({{ $project['days_remaining'] }} days)</li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                @else
                                                            <div class="alert alert-success mb-0">
                                                                <i class="bx bx-check-circle me-2"></i>
                                                                No significant risks identified
                                                            </div>
                                                @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('projects.show', $project['id']) }}" class="btn btn-primary">
                                        <i class="bx bx-show me-1"></i>View Full Project
                                    </a>
                                </div>
                                <div class="text-muted">
                                    <small>Last Updated: {{ $project['updated_at']->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            @if($projects->hasPages())
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} project(s)
                                </div>
                                <div>
                                    {{ $projects->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                <div class="text-center py-5">
                    <i class="bx bx-folder-open fs-1 text-muted"></i>
                    <h5 class="mt-3">No Projects Found</h5>
                            <p class="text-muted">No projects match your current filters or search criteria.</p>
                            <a href="{{ route('reports.projects.progress') }}" class="btn btn-primary">
                                <i class="bx bx-refresh me-1"></i>Clear Filters
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- / Content -->

<script>
function refreshReport() {
    location.reload();
}

function exportReport(format, type) {
    const baseUrl = '{{ url("reports/export") }}';
    const queryString = window.location.search;
    const url = `${baseUrl}/${format}/${type}${queryString}`;
    window.open(url, '_blank');
}
</script>

<style>
/* Enhanced Styling */
.project-report-card {
    border: 1px solid #e1e4e8;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: box-shadow 0.3s ease;
}

.project-report-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e1e4e8;
    padding: 1.5rem;
}

.stat-card {
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    color: white;
}

.avatar-lg {
    width: 3.5rem;
    height: 3.5rem;
    font-size: 1.5rem;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
    font-size: 0.875rem;
}

.progress {
    border-radius: 0.5rem;
    overflow: hidden;
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.6s ease;
}

.progress-circle {
    text-align: center;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 0.75rem 1rem;
}

.nav-tabs .nav-link:hover {
    color: #495057;
    border-bottom-color: #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #696cff;
    border-bottom-color: #696cff;
    background-color: transparent;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.timeline-info {
    font-size: 0.9375rem;
}

.bg-primary-subtle {
    background-color: rgba(105, 108, 255, 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(40, 199, 111, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 171, 0, 0.1) !important;
}

.bg-danger-subtle {
    background-color: rgba(255, 62, 29, 0.1) !important;
}

.bg-label-primary {
    background-color: rgba(105, 108, 255, 0.16);
    color: #696cff;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }

    .stat-card {
        margin-bottom: 0.75rem;
    }

    .table-responsive {
        font-size: 0.875rem;
    }
}

/* Print styles */
@media print {
    .btn, .dropdown, .nav-tabs {
        display: none !important;
    }

    .tab-pane {
        display: block !important;
        opacity: 1 !important;
    }

    .card {
        page-break-inside: avoid;
    }
}
</style>

<script>
// Manager Control Functions
function viewUserTasks(userId, projectId) {
    // Redirect to user's tasks filtered by project
    window.open(`{{ url('tasks') }}?user_id=${userId}&project_id=${projectId}`, '_blank');
}

function viewUserPerformance(userId) {
    // Redirect to user performance report
    window.open(`{{ url('reports/users') }}/${userId}`, '_blank');
}

function sendReminder(userId, projectId) {
    if (confirm('Send a reminder to this team member about their overdue tasks?')) {
        // Send AJAX request to send reminder
        fetch(`{{ url('api/send-reminder') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                user_id: userId,
                project_id: projectId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Reminder sent successfully!');
            } else {
                alert('Failed to send reminder: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send reminder. Please try again.');
        });
    }
}

function exportTeamPerformance(projectId) {
    // Export team performance data
    window.open(`{{ url('reports/projects') }}/${projectId}/export/team-performance`, '_blank');
}

function refreshTeamData(projectId) {
    // Refresh the current page to get updated data
    window.location.reload();
}

function exportProjectTasks(projectId) {
    // Export project tasks data
    window.open(`{{ url('reports/projects') }}/${projectId}/export/tasks`, '_blank');
}

function sendTaskReminder(taskId) {
    if (confirm('Send a reminder about this overdue task?')) {
        // Send AJAX request to send task reminder
        fetch(`{{ url('api/send-task-reminder') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                task_id: taskId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Task reminder sent successfully!');
            } else {
                alert('Failed to send reminder: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send reminder. Please try again.');
        });
    }
}

// Auto-refresh data every 5 minutes for managers
@if(auth()->user()->isManager() || auth()->user()->isSubAdmin())
setInterval(function() {
    // Only refresh if user is still on the page
    if (!document.hidden) {
        location.reload();
    }
}, 300000); // 5 minutes
@endif

// Enhanced task filtering and search
function filterTasks(projectId, status = 'all', priority = 'all') {
    const table = document.querySelector(`#tasks-${projectId} table tbody`);
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const statusCell = row.querySelector('td:nth-child(2) .badge');
        const priorityCell = row.querySelector('td:nth-child(3) .badge');

        let showRow = true;

        if (status !== 'all') {
            const taskStatus = statusCell.textContent.toLowerCase().replace(/\s+/g, '_');
            showRow = showRow && taskStatus.includes(status);
        }

        if (priority !== 'all') {
            const taskPriority = priorityCell.textContent.toLowerCase();
            showRow = showRow && taskPriority.includes(priority);
        }

        row.style.display = showRow ? '' : 'none';
    });
}

// Add filter controls to tasks tab
document.addEventListener('DOMContentLoaded', function() {
    // Add filter controls to each tasks tab
    const taskTabs = document.querySelectorAll('[id^="tasks-"]');
    taskTabs.forEach(tab => {
        const projectId = tab.id.split('-')[1];
        const header = tab.querySelector('h6');

        if (header) {
            const filterControls = document.createElement('div');
            filterControls.className = 'mb-3 d-flex gap-2 flex-wrap';
            filterControls.innerHTML = `
                <select class="form-select form-select-sm" style="width: auto;" onchange="filterTasks(${projectId}, this.value, document.querySelector('#priority-${projectId}').value)">
                    <option value="all">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="in_progress">In Progress</option>
                    <option value="pending">Pending</option>
                    <option value="assigned">Assigned</option>
                </select>
                <select id="priority-${projectId}" class="form-select form-select-sm" style="width: auto;" onchange="filterTasks(${projectId}, document.querySelector('#status-${projectId}').value, this.value)">
                    <option value="all">All Priority</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
                <button class="btn btn-sm btn-outline-secondary" onclick="filterTasks(${projectId}, 'all', 'all')">Clear Filters</button>
            `;

            header.parentNode.insertBefore(filterControls, header.nextSibling);
        }
    });
});
</script>
@endsection
