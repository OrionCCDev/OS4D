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

    <!-- Debug Information (remove in production) -->
    @if(config('app.debug'))
    <div class="alert alert-info mb-4">
        <h6>Debug Information:</h6>
        <pre>{{ json_encode($taskReport, JSON_PRETTY_PRINT) }}</pre>
    </div>
    @endif

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
                                    <th>Due Date</th>
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
    document.getElementById('filterForm').reset();
    document.getElementById('filterForm').submit();
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
                    <div class="modal-header">
                        <h5 class="modal-title">Task History - Task #${taskId}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading task history...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            if (data.success && data.history) {
                modalBody.innerHTML = `
                    <div class="timeline">
                        ${data.history.map(entry => `
                            <div class="timeline-item">
                                <div class="timeline-marker bg-${entry.type === 'created' ? 'primary' : entry.type === 'updated' ? 'warning' : 'success'}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">${entry.title}</h6>
                                    <p class="text-muted mb-1">${entry.description}</p>
                                    <small class="text-muted">
                                        <i class="bx bx-time me-1"></i>
                                        ${new Date(entry.created_at).toLocaleString()}
                                        ${entry.user ? ` by ${entry.user.name}` : ''}
                                    </small>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else {
                modalBody.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bx bx-history fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No history found for this task</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading task history:', error);
            const modalBody = document.querySelector('#taskHistoryModal .modal-body');
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <i class="bx bx-error fs-1 text-danger"></i>
                    <p class="text-danger mt-2">Error loading task history</p>
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
