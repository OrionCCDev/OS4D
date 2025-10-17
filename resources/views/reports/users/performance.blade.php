@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">User Performance Reports</h4>
            <p class="text-muted">Track individual and team performance metrics</p>
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
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'users')">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'users')">Export as Excel</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.users') }}" id="filterForm">
                <div class="row g-3">
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

    <!-- Performance Rankings -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Performance Rankings</h5>
        </div>
        <div class="card-body">
            @if($rankings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Performance Score</th>
                                <th>Completion Rate</th>
                                <th>On-Time Rate</th>
                                <th>Tasks</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rankings as $ranking)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($ranking['rank'] <= 3)
                                                <i class="bx bx-trophy text-warning me-2"></i>
                                            @endif
                                            <span class="badge bg-{{ $ranking['rank'] <= 3 ? 'warning' : 'primary' }}">{{ $ranking['rank'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-primary">
                                                    {{ substr($ranking['user']['name'], 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $ranking['user']['name'] }}</h6>
                                                <small class="text-muted">{{ $ranking['user']['email'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 80px; height: 8px;">
                                                <div class="progress-bar bg-{{ $ranking['performance_score'] >= 80 ? 'success' : ($ranking['performance_score'] >= 60 ? 'warning' : 'danger') }}"
                                                     style="width: {{ $ranking['performance_score'] }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $ranking['performance_score'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $ranking['completion_rate'] }}%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $ranking['on_time_rate'] }}%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $ranking['completed_tasks'] }}/{{ $ranking['total_tasks'] }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $grade = $ranking['performance_score'] >= 90 ? 'A+' :
                                                    ($ranking['performance_score'] >= 80 ? 'A' :
                                                    ($ranking['performance_score'] >= 70 ? 'B+' :
                                                    ($ranking['performance_score'] >= 60 ? 'B' :
                                                    ($ranking['performance_score'] >= 50 ? 'C' : 'D'))));
                                        @endphp
                                        <span class="badge bg-{{ $ranking['performance_score'] >= 80 ? 'success' : ($ranking['performance_score'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ $grade }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('reports.users.performance', $ranking['user']['id']) }}">View Details</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="generateEvaluation({{ $ranking['user']['id'] }})">Generate Evaluation</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-user fs-1 text-muted"></i>
                    <h5 class="mt-3">No Performance Data</h5>
                    <p class="text-muted">No performance data available for the selected period.</p>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- / Content -->

<!-- Evaluation Modal -->
<div class="modal fade" id="evaluationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Evaluation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="evaluationForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="evaluation_type" class="form-label">Evaluation Type</label>
                        <select class="form-select" id="evaluation_type" name="evaluation_type" required>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="evaluation_year" class="form-label">Year</label>
                        <select class="form-select" id="evaluation_year" name="year" required>
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3" id="month_quarter_field">
                        <label for="evaluation_month" class="form-label">Month</label>
                        <select class="form-select" id="evaluation_month" name="month">
                            @for($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}" {{ $month == date('n') ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3" id="quarter_field" style="display: none;">
                        <label for="evaluation_quarter" class="form-label">Quarter</label>
                        <select class="form-select" id="evaluation_quarter" name="quarter">
                            <option value="1">Q1 (Jan-Mar)</option>
                            <option value="2">Q2 (Apr-Jun)</option>
                            <option value="3">Q3 (Jul-Sep)</option>
                            <option value="4">Q4 (Oct-Dec)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Evaluation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let selectedUserId = null;

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

function generateEvaluation(userId) {
    selectedUserId = userId;
    document.getElementById('evaluationForm').reset();
    document.getElementById('evaluation_year').value = new Date().getFullYear();
    document.getElementById('evaluation_month').value = new Date().getMonth() + 1;
    new bootstrap.Modal(document.getElementById('evaluationModal')).show();
}

document.getElementById('evaluation_type').addEventListener('change', function() {
    const monthField = document.getElementById('month_quarter_field');
    const quarterField = document.getElementById('quarter_field');

    if (this.value === 'quarterly') {
        monthField.style.display = 'none';
        quarterField.style.display = 'block';
    } else {
        monthField.style.display = 'block';
        quarterField.style.display = 'none';
    }
});

document.getElementById('evaluationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('user_id', selectedUserId);

    let url = '';
    if (formData.get('evaluation_type') === 'monthly') {
        url = '{{ route("reports.evaluations.monthly") }}';
    } else if (formData.get('evaluation_type') === 'quarterly') {
        url = '{{ route("reports.evaluations.quarterly") }}';
    } else {
        url = '{{ route("reports.evaluations.annual") }}';
    }

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('evaluationModal')).hide();
            location.reload();
        } else {
            alert('Error generating evaluation: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating evaluation');
    });
});
</script>
@endsection
