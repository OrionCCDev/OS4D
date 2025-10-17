@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">Reports & Analytics Dashboard</h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="bx bx-refresh me-1"></i>Refresh
            </button>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-download me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'dashboard')">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'dashboard')">Export as Excel</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-primary">
                                <i class="bx bx-folder-open"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">Total Projects</h6>
                            <h4 class="mb-0">{{ $summaryData['total_projects'] }}</h4>
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
                            <h6 class="mb-0">Active Projects</h6>
                            <h4 class="mb-0">{{ $summaryData['active_projects'] }}</h4>
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
                            <span class="avatar-initial rounded bg-info">
                                <i class="bx bx-task"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">Total Tasks</h6>
                            <h4 class="mb-0">{{ $summaryData['total_tasks'] }}</h4>
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
                            <h6 class="mb-0">Overdue Tasks</h6>
                            <h4 class="mb-0 text-warning">{{ $summaryData['overdue_tasks'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded bg-primary">
                            <i class="bx bx-folder-open fs-2"></i>
                        </span>
                    </div>
                    <h5 class="card-title">Project Reports</h5>
                    <p class="card-text">View project progress, timelines, and resource allocation</p>
                    <a href="{{ route('reports.projects') }}" class="btn btn-primary">View Reports</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded bg-info">
                            <i class="bx bx-task fs-2"></i>
                        </span>
                    </div>
                    <h5 class="card-title">Task Reports</h5>
                    <p class="card-text">Analyze task completion rates and performance metrics</p>
                    <a href="{{ route('reports.tasks') }}" class="btn btn-info">View Reports</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded bg-success">
                            <i class="bx bx-user fs-2"></i>
                        </span>
                    </div>
                    <h5 class="card-title">User Performance</h5>
                    <p class="card-text">Track individual and team performance metrics</p>
                    <a href="{{ route('reports.users') }}" class="btn btn-success">View Reports</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mx-auto mb-3">
                        <span class="avatar-initial rounded bg-warning">
                            <i class="bx bx-trophy fs-2"></i>
                        </span>
                    </div>
                    <h5 class="card-title">Evaluations</h5>
                    <p class="card-text">Generate and manage employee evaluations</p>
                    <a href="{{ route('reports.evaluations') }}" class="btn btn-warning">View Reports</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers and Recent Evaluations -->
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Performers This Month</h5>
                </div>
                <div class="card-body">
                    @if($topPerformers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Name</th>
                                        <th>Score</th>
                                        <th>Tasks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topPerformers as $performer)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $performer['rank'] }}</span>
                                            </td>
                                            <td>{{ $performer['user']['name'] }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar" style="width: {{ $performer['performance_score'] }}%"></div>
                                                    </div>
                                                    <span class="small">{{ $performer['performance_score'] }}%</span>
                                                </div>
                                            </td>
                                            <td>{{ $performer['completed_tasks'] }}/{{ $performer['total_tasks'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-trophy fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No performance data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Evaluations</h5>
                </div>
                <div class="card-body">
                    @if($recentEvaluations->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentEvaluations as $evaluation)
                                <div class="list-group-item px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-{{ $evaluation->performance_score >= 80 ? 'success' : ($evaluation->performance_score >= 60 ? 'warning' : 'danger') }}">
                                                {{ substr($evaluation->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $evaluation->user->name }}</h6>
                                            <p class="mb-1 small text-muted">
                                                {{ ucfirst($evaluation->evaluation_type) }} - {{ $evaluation->performance_score }}%
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $evaluation->performance_score >= 80 ? 'success' : ($evaluation->performance_score >= 60 ? 'warning' : 'danger') }}">
                                                {{ $evaluation->performance_grade }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-file-blank fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No evaluations available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->

<script>
function refreshDashboard() {
    location.reload();
}

function exportReport(format, type) {
    const baseUrl = '{{ url("reports/export") }}';
    const url = `${baseUrl}/${format}/${type}`;
    window.open(url, '_blank');
}
</script>
@endsection
