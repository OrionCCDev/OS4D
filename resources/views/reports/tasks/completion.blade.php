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
                            @foreach(\App\Models\Project::all() as $project)
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
                            @foreach(\App\Models\User::where('role', '!=', 'admin')->get() as $user)
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
                    <div class="col-md-6">
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
                            <h4 class="mb-0">{{ $taskReport['total_tasks'] }}</h4>
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
                            <h4 class="mb-0">{{ $taskReport['completed_tasks'] }}</h4>
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
                            <h4 class="mb-0">{{ $taskReport['in_progress_tasks'] }}</h4>
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
                            <h4 class="mb-0 text-danger">{{ $taskReport['overdue_tasks'] }}</h4>
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
</script>
@endsection
