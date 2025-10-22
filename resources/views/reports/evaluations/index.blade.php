@extends('layouts.app')

@section('content')
<!-- Content -->
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Employee Evaluations</h4>
            <p class="text-muted">Generate and manage employee performance evaluations</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="showGenerateModal()">
                <i class="bx bx-plus me-1"></i>Generate Evaluation
            </button>
            <button class="btn btn-outline-primary" onclick="refreshReport()">
                <i class="bx bx-refresh me-1"></i>Refresh
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-download me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'evaluations')">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'evaluations')">Export as Excel</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.evaluations') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Evaluation Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="monthly" {{ $evaluationType === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ $evaluationType === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="annual" {{ $evaluationType === 'annual' ? 'selected' : '' }}>Annual</option>
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

    <!-- Evaluations Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Evaluations</h5>
        </div>
        <div class="card-body">
            @if($evaluations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Performance Score</th>
                                <th>Grade</th>
                                <th>Tasks Completed</th>
                                <th>Status</th>
                                <th>Evaluated By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluations as $evaluation)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded bg-primary">
                                                    {{ substr($evaluation->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $evaluation->user->name }}</h6>
                                                <small class="text-muted">{{ $evaluation->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $evaluation->evaluation_type === 'monthly' ? 'info' : ($evaluation->evaluation_type === 'quarterly' ? 'warning' : 'success') }}">
                                            {{ ucfirst($evaluation->evaluation_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $evaluation->evaluation_period_start->format('M d') }} -
                                            {{ $evaluation->evaluation_period_end->format('M d, Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 80px; height: 8px;">
                                                <div class="progress-bar bg-{{ $evaluation->performance_score >= 80 ? 'success' : ($evaluation->performance_score >= 60 ? 'warning' : 'danger') }}"
                                                     style="width: {{ $evaluation->performance_score }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $evaluation->performance_score }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $evaluation->performance_score >= 80 ? 'success' : ($evaluation->performance_score >= 60 ? 'warning' : 'danger') }}">
                                            {{ $evaluation->performance_grade }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $evaluation->tasks_completed }}</span>
                                        @if($evaluation->overdue_tasks > 0)
                                            <span class="badge bg-danger ms-1">{{ $evaluation->overdue_tasks }} overdue</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $evaluation->status === 'approved' ? 'success' : ($evaluation->status === 'submitted' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($evaluation->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $evaluation->evaluator->name }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="viewEvaluation({{ $evaluation->id }})">View Details</a></li>
                                                @if($evaluation->status === 'draft')
                                                    <li><a class="dropdown-item" href="#" onclick="editEvaluation({{ $evaluation->id }})">Edit</a></li>
                                                @endif
                                                @if($evaluation->status === 'submitted')
                                                    <li><a class="dropdown-item" href="#" onclick="approveEvaluation({{ $evaluation->id }})">Approve</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $evaluations->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-file-blank fs-1 text-muted"></i>
                    <h5 class="mt-3">No Evaluations Found</h5>
                    <p class="text-muted">No evaluations match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- / Content -->

<!-- Generate Evaluation Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Evaluation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="generateForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Employee</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select Employee</option>
                            @foreach(\App\Models\User::where('role', '!=', 'admin')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Evaluation</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

function showGenerateModal() {
    document.getElementById('generateForm').reset();
    document.getElementById('evaluation_year').value = new Date().getFullYear();
    document.getElementById('evaluation_month').value = new Date().getMonth() + 1;
    new bootstrap.Modal(document.getElementById('generateModal')).show();
}

function viewEvaluation(id) {
    // This would open a detailed view of the evaluation
    alert('View evaluation details for ID: ' + id);
}

function editEvaluation(id) {
    // This would open an edit form for the evaluation
    alert('Edit evaluation for ID: ' + id);
}

function approveEvaluation(id) {
    if (confirm('Are you sure you want to approve this evaluation?')) {
        // This would send an approval request
        alert('Evaluation approved for ID: ' + id);
    }
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

document.getElementById('generateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

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
            bootstrap.Modal.getInstance(document.getElementById('generateModal')).hide();
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
