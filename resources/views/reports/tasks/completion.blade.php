@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Task Reports</h4>
            <p class="text-muted">Analyze task completion rates and performance metrics</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshReport()">
                <i class="bx bx-refresh me-1"></i>Refresh
            </button>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-download me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'tasks')">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'tasks')">Export as Excel</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.tasks') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ $filters['project_id'] == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">User</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $filters['user_id'] == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status[]" multiple>
                            <option value="pending" {{ in_array('pending', $filters['status'] ?? []) ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ in_array('in_progress', $filters['status'] ?? []) ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ in_array('completed', $filters['status'] ?? []) ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ in_array('cancelled', $filters['status'] ?? []) ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority[]" multiple>
                            <option value="high" {{ in_array('high', $filters['priority'] ?? []) ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ in_array('medium', $filters['priority'] ?? []) ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ in_array('low', $filters['priority'] ?? []) ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search Tasks</label>
                        <input type="text" class="form-control" id="search" name="search"
                               placeholder="Search by task name, description, project, or assignee..."
                               value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search me-1"></i>Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="bx bx-x me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-info">
                                <i class="bx bx-task"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">Total Tasks</h6>
                            <h4 class="mb-0">{{ $taskReport['total_tasks'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-success">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">Completed</h6>
                            <h4 class="mb-0">{{ $taskReport['completed_tasks'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-warning">
                                <i class="bx bx-time"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">In Progress</h6>
                            <h4 class="mb-0">{{ $taskReport['in_progress_tasks'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-danger">
                                <i class="bx bx-error"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">Overdue</h6>
                            <h4 class="mb-0 text-danger">{{ $taskReport['overdue_tasks'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tasks by Priority</h5>
                </div>
                <div class="card-body">
                    @if(isset($taskReport['tasks_by_priority']) && count($taskReport['tasks_by_priority']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Priority</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($taskReport['tasks_by_priority'] as $priority => $count)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning' : 'info') }}">
                                                    {{ ucfirst($priority) }}
                                                </span>
                                            </td>
                                            <td>{{ $count }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar" style="width: {{ $taskReport['total_tasks'] > 0 ? round(($count / $taskReport['total_tasks']) * 100, 1) : 0 }}%"></div>
                                                    </div>
                                                    <span class="small">{{ $taskReport['total_tasks'] > 0 ? round(($count / $taskReport['total_tasks']) * 100, 1) : 0 }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-pie-chart fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No priority data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tasks by Status</h5>
                </div>
                <div class="card-body">
                    @if(isset($taskReport['tasks_by_status']) && count($taskReport['tasks_by_status']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($taskReport['tasks_by_status'] as $status => $count)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $status === 'completed' ? 'success' : ($status === 'in_progress' ? 'warning' : ($status === 'pending' ? 'info' : 'danger')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $count }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar" style="width: {{ $taskReport['total_tasks'] > 0 ? round(($count / $taskReport['total_tasks']) * 100, 1) : 0 }}%"></div>
                                                    </div>
                                                    <span class="small">{{ $taskReport['total_tasks'] > 0 ? round(($count / $taskReport['total_tasks']) * 100, 1) : 0 }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-pie-chart fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No status data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Completion Rate</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <div class="progress mx-auto" style="width: 150px; height: 150px;">
                                <div class="progress-bar bg-success" style="width: {{ $taskReport['completion_rate'] }}%"></div>
                            </div>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <h2 class="mb-0">{{ $taskReport['completion_rate'] }}%</h2>
                                <small class="text-muted">Completion Rate</small>
                            </div>
                        </div>
                        <p class="text-muted">Overall task completion rate for the selected period</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Average Completion Time</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="bx bx-time fs-1 text-primary"></i>
                        </div>
                        <h3 class="mb-1">{{ $taskReport['average_completion_time'] }} hours</h3>
                        <p class="text-muted">Average time to complete a task</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task List -->
    @if(isset($taskReport['tasks']) && $taskReport['tasks']->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Task List</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Showing {{ $taskReport['tasks']->firstItem() }} to {{ $taskReport['tasks']->lastItem() }} of {{ $taskReport['tasks']->total() }} tasks</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Assignee</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Start Date</th>
                                    <th>Due Date</th>
                                    <th>Duration</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($taskReport['tasks'] as $task)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1">{{ $task->title }}</h6>
                                            @if($task->description)
                                                <small class="text-muted">{{ Str::limit($task->description, 60) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($task->project)
                                            <span class="badge bg-info">{{ $task->project->name }}</span>
                                        @else
                                            <span class="text-muted">No Project</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->assignee)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <img src="{{ asset('uploads/users/' . ($task->assignee->img ?: 'default.png')) }}"
                                                         alt="{{ $task->assignee->name }}" class="rounded-circle">
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $task->assignee->name }}</div>
                                                    <small class="text-muted">{{ $task->assignee->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : ($task->status === 'pending' ? 'info' : 'danger')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}">
                                            {{ ucfirst($task->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($task->start_date)
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold text-info">{{ \Carbon\Carbon::parse($task->start_date)->format('M d, Y') }}</span>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($task->start_date)->format('H:i') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">No start date</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->due_date)
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($task->due_date)->format('H:i') }}</small>
                                                @if($task->due_date < now() && $task->status !== 'completed')
                                                    <small class="text-danger">Overdue</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">No due date</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->start_date && $task->due_date)
                                            @php
                                                $startDate = \Carbon\Carbon::parse($task->start_date);
                                                $dueDate = \Carbon\Carbon::parse($task->due_date);
                                                $duration = $startDate->diffInDays($dueDate);
                                            @endphp
                                            <span class="fw-semibold text-primary">
                                                {{ $duration }} {{ $duration == 1 ? 'day' : 'days' }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ \Carbon\Carbon::parse($task->created_at)->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($task->created_at)->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewTaskHistory({{ $task->id }})" title="View Task History">
                                                <i class="bx bx-history"></i>
                                            </button>
                                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-info" title="View Task Details">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $taskReport['tasks']->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-task fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">No tasks found</h5>
                    <p class="text-muted">Try adjusting your filters or search criteria to find tasks.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
<!-- / Content -->

<script>
function refreshReport() {
    location.reload();
}

function clearFilters() {
    // Clear all form fields
    document.getElementById('filterForm').reset();

    // Clear multiple select fields explicitly
    const statusSelect = document.getElementById('status');
    const prioritySelect = document.getElementById('priority');

    if (statusSelect) {
        Array.from(statusSelect.options).forEach(option => {
            option.selected = false;
        });
    }

    if (prioritySelect) {
        Array.from(prioritySelect.options).forEach(option => {
            option.selected = false;
        });
    }

    // Redirect to clean URL without any parameters
    window.location.href = '{{ route("reports.tasks") }}';
}

function exportReport(format, type) {
    const baseUrl = '{{ url("reports/export") }}';
    const url = `${baseUrl}/${format}/${type}`;
    window.open(url, '_blank');
}

function viewTaskHistory(taskId) {
    // Create a modal to show task history
    const modalHtml = `
        <div class="modal fade" id="taskHistoryModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary text-white">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bx bx-history me-2"></i>
                            Task History - Task #${taskId}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading task history...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
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

            .timeline-meta {
                border-top: 1px solid #f8f9fa;
                padding-top: 10px;
                margin-top: 10px;
            }

            .bg-gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            }

            .modal-lg {
                max-width: 800px;
            }

            .timeline-item:last-child {
                margin-bottom: 0;
            }

            .timeline-item:last-child::after {
                content: '';
                position: absolute;
                left: 30px;
                top: 25px;
                bottom: -20px;
                width: 2px;
                background: #fff;
                z-index: 1;
            }
        </style>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('taskHistoryModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('taskHistoryModal'));
    modal.show();

    // Load task history via AJAX
    fetch(`/tasks/${taskId}/history`)
        .then(response => response.json())
        .then(data => {
            const modalBody = document.querySelector('#taskHistoryModal .modal-body');
            if (data.success && data.history && data.history.length > 0) {
                modalBody.innerHTML = `
                    <div class="timeline">
                        ${data.history.map(entry => {
                            const title = entry.title || 'Task Update';
                            const description = entry.description || '';
                            const type = entry.type || 'updated';
                            const user = entry.user ? entry.user.name : 'System';
                            const date = new Date(entry.created_at);

                            // Get type-specific styling with better colors
                            let typeClass = 'secondary';
                            let typeIcon = 'bx-edit';
                            let typeLabel = 'Updated';
                            let typeColor = '#6c757d';

                            switch(type) {
                                case 'created':
                                    typeClass = 'primary';
                                    typeIcon = 'bx-plus-circle';
                                    typeLabel = 'Created';
                                    typeColor = '#0d6efd';
                                    break;
                                case 'status_changed':
                                    typeClass = 'info';
                                    typeIcon = 'bx-refresh';
                                    typeLabel = 'Status Changed';
                                    typeColor = '#0dcaf0';
                                    break;
                                case 'assigned':
                                    typeClass = 'warning';
                                    typeIcon = 'bx-user-plus';
                                    typeLabel = 'Assigned';
                                    typeColor = '#ffc107';
                                    break;
                                case 'completed':
                                    typeClass = 'success';
                                    typeIcon = 'bx-check-circle';
                                    typeLabel = 'Completed';
                                    typeColor = '#198754';
                                    break;
                                case 'approved':
                                    typeClass = 'success';
                                    typeIcon = 'bx-check-double';
                                    typeLabel = 'Approved';
                                    typeColor = '#198754';
                                    break;
                                case 'rejected':
                                    typeClass = 'danger';
                                    typeIcon = 'bx-x-circle';
                                    typeLabel = 'Rejected';
                                    typeColor = '#dc3545';
                                    break;
                                case 'review':
                                    typeClass = 'info';
                                    typeIcon = 'bx-search';
                                    typeLabel = 'Review';
                                    typeColor = '#17a2b8';
                                    break;
                                case 'submitted':
                                    typeClass = 'primary';
                                    typeIcon = 'bx-send';
                                    typeLabel = 'Submitted';
                                    typeColor = '#0d6efd';
                                    break;
                                case 'accepted':
                                    typeClass = 'success';
                                    typeIcon = 'bx-check';
                                    typeLabel = 'Accepted';
                                    typeColor = '#198754';
                                    break;
                                case 'override':
                                    typeClass = 'warning';
                                    typeIcon = 'bx-shield';
                                    typeLabel = 'Override';
                                    typeColor = '#ffc107';
                                    break;
                                case 'approval':
                                    typeClass = 'success';
                                    typeIcon = 'bx-check-double';
                                    typeLabel = 'Approval';
                                    typeColor = '#198754';
                                    break;
                                case 'email':
                                    typeClass = 'info';
                                    typeIcon = 'bx-envelope';
                                    typeLabel = 'Email';
                                    typeColor = '#17a2b8';
                                    break;
                                case 'attachment':
                                    typeClass = 'secondary';
                                    typeIcon = 'bx-paperclip';
                                    typeLabel = 'Attachment';
                                    typeColor = '#6c757d';
                                    break;
                            }

                            return `
                                <div class="timeline-item">
                                    <div class="timeline-marker" style="background-color: ${typeColor};">
                                        <i class="bx ${typeIcon} text-white"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0" style="color: ${typeColor}; font-weight: 600;">${title}</h6>
                                            <span class="badge" style="background-color: ${typeColor}20; color: ${typeColor}; border: 1px solid ${typeColor}40;">${typeLabel}</span>
                                        </div>
                                        ${description ? `<p class="mb-2 small" style="color: #6c757d; line-height: 1.4;">${description}</p>` : ''}
                                        <div class="timeline-meta">
                                            <small style="color: #8e9297;">
                                                <i class="bx bx-time me-1"></i>
                                                ${date.toLocaleString('en-US', {
                                                    year: 'numeric',
                                                    month: 'short',
                                                    day: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                    hour12: true
                                                })}
                                                <span class="ms-2">
                                                    <i class="bx bx-user me-1"></i>
                                                    ${user}
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                `;
            } else {
                modalBody.innerHTML = `
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="bx bx-history fs-1 text-muted opacity-50"></i>
                        </div>
                        <h6 class="text-muted">No History Available</h6>
                        <p class="text-muted small">This task doesn't have any recorded history yet.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading task history:', error);
            const modalBody = document.querySelector('#taskHistoryModal .modal-body');
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bx bx-error-circle fs-1 text-danger opacity-50"></i>
                    </div>
                    <h6 class="text-danger">Error Loading History</h6>
                    <p class="text-muted small">Unable to load task history. Please try again later.</p>
                    <button class="btn btn-outline-primary btn-sm mt-2" onclick="viewTaskHistory(${taskId})">
                        <i class="bx bx-refresh me-1"></i>Retry
                    </button>
                </div>
            `;
        });
}

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const filterInputs = filterForm.querySelectorAll('select, input[type="date"]');

    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            filterForm.submit();
        });
    });
});
</script>
@endsection
