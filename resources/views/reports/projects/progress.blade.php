@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Project Progress Report</h4>
            <p class="text-muted">Detailed project progress analysis and task tracking</p>
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

    <!-- Project Progress Cards -->
    <div class="row mb-4">
        @if($projects->count() > 0)
            @foreach($projects as $project)
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded bg-primary">
                                        <i class="bx bx-folder-open"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ $project['name'] }}</h5>
                                    <small class="text-muted">Code: {{ $project['short_code'] }} | Created {{ $project['created_at']->format('M d, Y') }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $project['status'] === 'active' ? 'success' : ($project['status'] === 'completed' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($project['status']) }}
                                </span>
                                <div class="progress" style="width: 150px; height: 8px;">
                                    <div class="progress-bar" style="width: {{ $project['completion_percentage'] }}%"></div>
                                </div>
                                <span class="small fw-semibold">{{ $project['completion_percentage'] }}%</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Project Statistics -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-info text-white me-3">
                                            <i class="bx bx-task"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $project['total_tasks'] }}</h6>
                                            <small class="text-muted">Total Tasks</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-success text-white me-3">
                                            <i class="bx bx-check-circle"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $project['completed_tasks'] }}</h6>
                                            <small class="text-muted">Completed</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-warning text-white me-3">
                                            <i class="bx bx-time"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $project['total_tasks'] - $project['completed_tasks'] }}</h6>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-danger text-white me-3">
                                            <i class="bx bx-exclamation-triangle"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $project['overdue_tasks'] }}</h6>
                                            <small class="text-muted">Overdue</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Team Members -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-3">Team Members ({{ $project['team_size'] }})</h6>
                                    @if(count($project['users_involved']) > 0)
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($project['users_involved'] as $user)
                                                <span class="badge bg-light text-dark">{{ $user }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">No team members assigned</p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-3">Project Details</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Sub Folders:</small>
                                            <div class="fw-semibold">{{ $project['sub_folders_count'] }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Due Date:</small>
                                            <div class="fw-semibold">
                                                @if($project['due_date'])
                                                    {{ \Carbon\Carbon::parse($project['due_date'])->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">No due date</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Timeline -->
                            <div class="mt-4">
                                <h6 class="mb-3">Progress Timeline</h6>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $project['completion_percentage'] }}%">
                                        {{ $project['completion_percentage'] }}% Complete
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted">Started: {{ $project['created_at']->format('M d, Y') }}</small>
                                    @if($project['due_date'])
                                        <small class="text-muted">Due: {{ \Carbon\Carbon::parse($project['due_date'])->format('M d, Y') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bx bx-folder-open fs-1 text-muted"></i>
                    <h5 class="mt-3">No Projects Found</h5>
                    <p class="text-muted">No projects match your current filters.</p>
                    <a href="{{ route('reports.projects') }}" class="btn btn-primary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Projects
                    </a>
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
    const url = `${baseUrl}/${format}/${type}`;
    window.open(url, '_blank');
}
</script>

<style>
/* Enhanced card styling */
.card {
    border: 1px solid #e1e4e8;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e1e4e8;
}

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.375rem;
    font-size: 1rem;
}

.progress {
    border-radius: 0.5rem;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.3s ease;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .card-header .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }

    .progress {
        width: 100% !important;
    }
}
</style>
@endsection
