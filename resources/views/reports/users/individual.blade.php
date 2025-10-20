@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.users') }}">User Performance</a></li>
                    <li class="breadcrumb-item active">{{ $userReport['user']['name'] }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">{{ $userReport['user']['name'] }}'s Performance Report</h4>
            <p class="text-muted">{{ $userReport['user']['email'] }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.users') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back
            </a>
            <button class="btn btn-outline-primary" onclick="refreshReport()">
                <i class="bx bx-refresh me-1"></i>Refresh
            </button>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" onclick="exportReport('pdf', 'user-{{ $userReport['user']['id'] }}')">
                    <i class="bx bx-file-pdf me-1"></i>Export PDF
                </button>
                <button class="btn btn-success" onclick="exportReport('excel', 'user-{{ $userReport['user']['id'] }}')">
                    <i class="bx bx-file-excel me-1"></i>Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.users.performance', $userReport['user']['id']) }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search me-1"></i>Apply Filters
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

    <!-- Performance Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Tasks -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-primary">
                            <i class="bx bx-task fs-4"></i>
                        </div>
                        <span class="badge bg-label-primary">Tasks</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $userReport['total_tasks'] }}</h4>
                    <p class="text-muted mb-0 small">Total Tasks</p>
                </div>
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-success">
                            <i class="bx bx-check-circle fs-4"></i>
                        </div>
                        <span class="badge bg-label-success">Completed</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $userReport['completed_tasks'] }}</h4>
                    <p class="text-muted mb-0 small">Completed Tasks</p>
                </div>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-info">
                            <i class="bx bx-trending-up fs-4"></i>
                        </div>
                        <span class="badge bg-label-info">Rate</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $userReport['completion_rate'] }}%</h4>
                    <p class="text-muted mb-0 small">Completion Rate</p>
                </div>
            </div>
        </div>

        <!-- Performance Score -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-warning">
                            <i class="bx bx-star fs-4"></i>
                        </div>
                        @php
                            $grade = $userReport['performance_score'] >= 90 ? 'A+' :
                                    ($userReport['performance_score'] >= 80 ? 'A' :
                                    ($userReport['performance_score'] >= 70 ? 'B+' :
                                    ($userReport['performance_score'] >= 60 ? 'B' :
                                    ($userReport['performance_score'] >= 50 ? 'C' : 'D'))));
                        @endphp
                        <span class="badge bg-{{ $userReport['performance_score'] >= 80 ? 'success' : ($userReport['performance_score'] >= 60 ? 'warning' : 'danger') }}">
                            {{ $grade }}
                        </span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $userReport['performance_score'] }}%</h4>
                    <p class="text-muted mb-0 small">Performance Score</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- On-Time Tasks -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-success">
                            <i class="bx bx-time-five fs-4"></i>
                        </div>
                        <span class="badge bg-label-success">On-Time</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $userReport['on_time_tasks'] }}</h4>
                    <p class="text-muted mb-0 small">On-Time Completions ({{ $userReport['on_time_rate'] }}%)</p>
                </div>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-danger">
                            <i class="bx bx-error-circle fs-4"></i>
                        </div>
                        <span class="badge bg-label-danger">Overdue</span>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $userReport['overdue_tasks'] }}</h4>
                    <p class="text-muted mb-0 small">Overdue Tasks</p>
                </div>
            </div>
        </div>

        <!-- Average Completion Time -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-primary">
                            <i class="bx bx-time fs-4"></i>
                        </div>
                        <span class="badge bg-label-primary">Average</span>
                    </div>
                    <h4 class="fw-bold mb-0">
                        {{ $userReport['average_completion_time'] > 0 ? number_format($userReport['average_completion_time'], 1) : '0' }}
                    </h4>
                    <p class="text-muted mb-0 small">Avg. Completion Time (days)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row g-4 mb-4">
        <!-- Tasks by Priority -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tasks by Priority</h5>
                </div>
                <div class="card-body">
                    @if(!empty($userReport['tasks_by_priority']) && $userReport['tasks_by_priority']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    @foreach(['urgent' => 'Urgent', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $key => $label)
                                        @php
                                            $count = $userReport['tasks_by_priority']->get($key, 0);
                                            $percentage = $userReport['total_tasks'] > 0 ? round(($count / $userReport['total_tasks']) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td class="py-2">
                                                <span class="badge bg-{{ $key == 'urgent' ? 'danger' : ($key == 'high' ? 'warning' : ($key == 'medium' ? 'info' : 'secondary')) }}">
                                                    {{ $label }}
                                                </span>
                                            </td>
                                            <td class="py-2">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-{{ $key == 'urgent' ? 'danger' : ($key == 'high' ? 'warning' : ($key == 'medium' ? 'info' : 'secondary')) }}"
                                                         style="width: {{ $percentage }}%">
                                                        {{ $count }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-2 text-end">
                                                <strong>{{ $percentage }}%</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-chart fs-1 text-muted"></i>
                            <p class="text-muted mt-3">No priority data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Performance Overview</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Completion Rate</span>
                            <strong>{{ $userReport['completion_rate'] }}%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $userReport['completion_rate'] >= 80 ? 'success' : ($userReport['completion_rate'] >= 60 ? 'warning' : 'danger') }}"
                                 style="width: {{ $userReport['completion_rate'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">On-Time Rate</span>
                            <strong>{{ $userReport['on_time_rate'] }}%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $userReport['on_time_rate'] >= 80 ? 'success' : ($userReport['on_time_rate'] >= 60 ? 'warning' : 'danger') }}"
                                 style="width: {{ $userReport['on_time_rate'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Performance Score</span>
                            <strong>{{ $userReport['performance_score'] }}%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $userReport['performance_score'] >= 80 ? 'success' : ($userReport['performance_score'] >= 60 ? 'warning' : 'danger') }}"
                                 style="width: {{ $userReport['performance_score'] }}%"></div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-top">
                        <h6 class="mb-3">Quick Stats</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-check-circle text-success me-2 fs-5"></i>
                                    <div>
                                        <div class="small text-muted">Completed</div>
                                        <strong>{{ $userReport['completed_tasks'] }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-error-circle text-danger me-2 fs-5"></i>
                                    <div>
                                        <div class="small text-muted">Overdue</div>
                                        <strong>{{ $userReport['overdue_tasks'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Generate Performance Evaluation</h5>
                    <p class="text-muted mb-0">Create a formal evaluation report for this user</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="generateEvaluation({{ $userReport['user']['id'] }})">
                    <i class="bx bx-file-plus me-2"></i>Generate Evaluation
                </button>
            </div>
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
let selectedUserId = {{ $userReport['user']['id'] }};

function refreshReport() {
    location.reload();
}

function clearFilters() {
    // Clear all form fields
    document.getElementById('filterForm').reset();

    // Redirect to clean URL without any parameters
    window.location.href = '{{ route("reports.users.performance", $userReport['user']['id']) }}';
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
    } else if (this.value === 'annual') {
        monthField.style.display = 'none';
        quarterField.style.display = 'none';
    } else {
        monthField.style.display = 'block';
        quarterField.style.display = 'none';
    }
});

document.getElementById('evaluationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('user_id', selectedUserId);

    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Generating...';
    submitButton.disabled = true;

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
        // Restore button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('evaluationModal')).hide();

            // Show success message
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed';
            successAlert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            successAlert.innerHTML = `
                <i class="bx bx-check-circle me-2"></i>
                <strong>Success!</strong> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(successAlert);

            // Auto-hide after 3 seconds
            setTimeout(() => {
                if (successAlert.parentNode) {
                    successAlert.remove();
                }
            }, 5000);
        } else {
            alert('Error generating evaluation: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        // Restore button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;

        console.error('Error:', error);
        alert('Error generating evaluation');
    });
});
</script>
@endsection

