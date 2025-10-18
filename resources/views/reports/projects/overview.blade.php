@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Project Reports</h4>
            <p class="text-muted">Comprehensive project analysis and progress tracking</p>
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
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'projects')">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'projects')">Export as Excel</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.projects') }}" id="filterForm">
                <!-- Search Bar -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search Projects</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bx bx-search"></i>
                            </span>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="Search by project name or short code..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status[]" multiple>
                            <option value="active" {{ in_array('active', $filters['status'] ?? []) ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ in_array('completed', $filters['status'] ?? []) ? 'selected' : '' }}>Completed</option>
                            <option value="on_hold" {{ in_array('on_hold', $filters['status'] ?? []) ? 'selected' : '' }}>On Hold</option>
                            <option value="cancelled" {{ in_array('cancelled', $filters['status'] ?? []) ? 'selected' : '' }}>Cancelled</option>
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

    <!-- Projects Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Project Overview</h5>
            <span class="badge bg-primary">{{ $projects->total() }} Projects</span>
        </div>
        <div class="card-body">
            @if($projects->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Project Details</th>
                                <th>Status</th>
                                <th>Owner</th>
                                <th>Progress</th>
                                <th>Tasks</th>
                                <th>Sub Folders</th>
                                <th>Team Members</th>
                                <th>Due Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-primary">
                                                    <i class="bx bx-folder-open"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $project['name'] }}</h6>
                                                <small class="text-muted">
                                                    Code: {{ $project['short_code'] }} |
                                                    Created {{ $project['created_at']->format('M d, Y') }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $project['status'] === 'active' ? 'success' : ($project['status'] === 'completed' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($project['status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $project['owner'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 80px; height: 8px;">
                                                <div class="progress-bar" style="width: {{ $project['completion_percentage'] }}%"></div>
                                            </div>
                                            <span class="small">{{ $project['completion_percentage'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-info mb-1">{{ $project['completed_tasks'] }}/{{ $project['total_tasks'] }}</span>
                                            @if($project['overdue_tasks'] > 0)
                                                <span class="badge bg-danger">{{ $project['overdue_tasks'] }} overdue</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $project['sub_folders_count'] }} folders</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-light text-dark">{{ $project['team_size'] }} members</span>
                                            @if(count($project['users_involved']) > 0)
                                                <small class="text-muted mt-1">
                                                    {{ implode(', ', array_slice($project['users_involved'], 0, 2)) }}
                                                    @if(count($project['users_involved']) > 2)
                                                        +{{ count($project['users_involved']) - 2 }} more
                                                    @endif
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($project['due_date'])
                                            <span class="small">{{ \Carbon\Carbon::parse($project['due_date'])->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-muted">No due date</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('projects.show', $project['id']) }}">View Details</a></li>
                                                <li><a class="dropdown-item" href="{{ route('reports.projects.progress', ['project_id' => $project['id']]) }}">View Progress</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} projects
                    </div>
                    <div>
                        {{ $projects->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-folder-open fs-1 text-muted"></i>
                    <h5 class="mt-3">No Projects Found</h5>
                    <p class="text-muted">No projects match your current search and filters.</p>
                    @if(request('search'))
                        <button class="btn btn-outline-primary" onclick="clearSearch()">
                            <i class="bx bx-x me-1"></i>Clear Search
                        </button>
                    @endif
                </div>
            @endif
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

function clearSearch() {
    document.getElementById('search').value = '';
    document.getElementById('filterForm').submit();
}

function exportReport(format, type) {
    const baseUrl = '{{ url("reports/export") }}';
    const url = `${baseUrl}/${format}/${type}`;
    window.open(url, '_blank');
}

// Auto-submit search after 500ms delay
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('filterForm').submit();
    }, 500);
});
</script>

<style>
/* Enhanced table styling */
.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .table th,
    .table td {
        padding: 0.5rem;
    }
}

/* Search input styling */
.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

/* Pagination styling */
.pagination .page-link {
    color: #696cff;
    border-color: #e1e4e8;
}

.pagination .page-item.active .page-link {
    background-color: #696cff;
    border-color: #696cff;
}

.pagination .page-link:hover {
    background-color: #e1e4e8;
    border-color: #696cff;
}
</style>
@endsection
