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
            <button class="btn btn-warning" onclick="generateBulkEvaluation()">
                <i class="bx bx-file-plus me-1"></i>Evaluate All Users
            </button>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" onclick="exportReport('pdf', 'users')">
                    <i class="bx bx-file-pdf me-1"></i>Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.users') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-6">
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
                                            <span class="badge bg-primary">{{ $ranking['performance_score'] }}</span>
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
                                            // Grade calculation based on raw performance score
                                            // Adjust thresholds for raw scores (typically 0-200+ range)
                                            $grade = $ranking['performance_score'] >= 150 ? 'A+' :
                                                    ($ranking['performance_score'] >= 120 ? 'A' :
                                                    ($ranking['performance_score'] >= 100 ? 'B+' :
                                                    ($ranking['performance_score'] >= 80 ? 'B' :
                                                    ($ranking['performance_score'] >= 60 ? 'C' : 'D'))));
                                        @endphp
                                        <span class="badge bg-{{ $ranking['performance_score'] >= 120 ? 'success' : ($ranking['performance_score'] >= 80 ? 'warning' : 'danger') }}">
                                            {{ $grade }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('reports.users.performance', $ranking['user']['id']) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Details">
                                                <i class="bx bx-show me-1"></i>View
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="generateEvaluation({{ $ranking['user']['id'] }})"
                                                    title="Generate Evaluation">
                                                <i class="bx bx-file-plus me-1"></i>Evaluate
                                            </button>
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

<!-- Bulk Evaluation Modal -->
<div class="modal fade" id="bulkEvaluationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Bulk Evaluation for All Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkEvaluationForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        This will generate evaluations for all users and automatically download a comprehensive PDF report.
                    </div>
                    <div class="mb-3">
                        <label for="bulk_evaluation_type" class="form-label">Evaluation Type</label>
                        <select class="form-select" id="bulk_evaluation_type" name="evaluation_type" required>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_evaluation_year" class="form-label">Year</label>
                        <select class="form-select" id="bulk_evaluation_year" name="year" required>
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3" id="bulk_month_quarter_field">
                        <label for="bulk_evaluation_month" class="form-label">Month</label>
                        <select class="form-select" id="bulk_evaluation_month" name="month">
                            @for($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}" {{ $month == date('n') ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3" id="bulk_quarter_field" style="display: none;">
                        <label for="bulk_evaluation_quarter" class="form-label">Quarter</label>
                        <select class="form-select" id="bulk_evaluation_quarter" name="quarter">
                            <option value="1">Q1 (Jan-Mar)</option>
                            <option value="2">Q2 (Apr-Jun)</option>
                            <option value="3">Q3 (Jul-Sep)</option>
                            <option value="4">Q4 (Oct-Dec)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-file-plus me-1"></i>Generate & Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
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
    // Clear all form fields
    document.getElementById('filterForm').reset();

    // Redirect to clean URL without any parameters
    window.location.href = '{{ route("reports.users") }}';
}

function generateBulkEvaluation() {
    document.getElementById('bulkEvaluationForm').reset();
    document.getElementById('bulk_evaluation_year').value = new Date().getFullYear();
    document.getElementById('bulk_evaluation_month').value = new Date().getMonth() + 1;
    new bootstrap.Modal(document.getElementById('bulkEvaluationModal')).show();
}


// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const userSelect = document.getElementById('user_id');

    // Auto-submit on user selection change
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }
});

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

            // Auto-hide after 3 seconds and reload
            setTimeout(() => {
                if (successAlert.parentNode) {
                    successAlert.remove();
                }
                location.reload();
            }, 3000);
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

// Bulk evaluation type change handler
document.getElementById('bulk_evaluation_type').addEventListener('change', function() {
    const monthField = document.getElementById('bulk_month_quarter_field');
    const quarterField = document.getElementById('bulk_quarter_field');

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

// Bulk evaluation form submission
document.getElementById('bulkEvaluationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Generating PDF...';
    submitButton.disabled = true;

    const url = '{{ route("reports.evaluations.bulk.pdf") }}';

    // Check for CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token meta tag not found');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        alert('Security token not found. Please refresh the page and try again.');
        return;
    }

    // Debug: Log the URL and form data
    console.log('Submitting to URL:', url);
    console.log('Form data:', {
        evaluation_type: formData.get('evaluation_type'),
        year: formData.get('year'),
        month: formData.get('month'),
        quarter: formData.get('quarter'),
        _token: formData.get('_token')
    });

    let downloadSuccessful = false;

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        // Check if response is ok
        if (!response.ok) {
            // If it's not ok, try to get error message
            return response.text().then(text => {
                console.error('Error response:', text);
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                } catch (e) {
                    throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
                }
            });
        }

        // Check if response is a PDF (content-type should be application/pdf)
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);

        if (contentType && contentType.includes('application/pdf')) {
            return response.blob();
        } else {
            // If it's not a PDF, it might be an error response
            return response.text().then(text => {
                console.error('Non-PDF response:', text);
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || 'Unknown error occurred');
                } catch (e) {
                    throw new Error(text.substring(0, 200) || 'Unknown error occurred');
                }
            });
        }
    })
    .then(blob => {
        // Verify blob is valid
        if (!blob || blob.size === 0) {
            throw new Error('Received empty or invalid PDF file');
        }

        // Mark download as successful before attempting download
        downloadSuccessful = true;

        // Create a download link and trigger download
        const downloadUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = downloadUrl;

        // Get filename from form data
        const evaluationType = formData.get('evaluation_type');
        const year = formData.get('year');
        const month = formData.get('month') || '';
        const quarter = formData.get('quarter') || '';
        let periodLabel = year;

        if (evaluationType === 'monthly') {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            periodLabel = monthNames[month - 1] + '_' + year;
        } else if (evaluationType === 'quarterly') {
            periodLabel = 'Q' + quarter + '_' + year;
        }

        a.download = 'All_Users_Evaluation_' + evaluationType + '_' + periodLabel + '_' + new Date().toISOString().split('T')[0] + '.pdf';
        document.body.appendChild(a);
        a.click();

        // Clean up
        setTimeout(() => {
            window.URL.revokeObjectURL(downloadUrl);
            if (document.body.contains(a)) {
                document.body.removeChild(a);
            }
        }, 100);

        // Restore button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('bulkEvaluationModal'));
        if (modal) {
            modal.hide();
        }

        // Show success message
        const successAlert = document.createElement('div');
        successAlert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        successAlert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        successAlert.innerHTML = `
            <i class="bx bx-check-circle me-2"></i>
            <strong>Success!</strong> Bulk evaluation generated and downloaded successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(successAlert);

        // Auto-hide after 5 seconds and reload
        setTimeout(() => {
            if (successAlert.parentNode) {
                successAlert.remove();
            }
            location.reload();
        }, 5000);
    })
    .catch(error => {
        // Only show error if download was not successful
        if (!downloadSuccessful) {
            // Restore button state
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;

            console.error('Fetch Error:', error);
            console.error('Error stack:', error.stack);

            // Determine error type and message
            let errorMessage = 'Failed to generate evaluation';
            if (error.message) {
                if (error.message.includes('Failed to fetch')) {
                    errorMessage = 'Network error: Could not connect to server. Please check your internet connection and try again.';
                } else if (error.message.includes('NetworkError')) {
                    errorMessage = 'Network error: Request blocked. Please check your connection.';
                } else {
                    errorMessage = error.message;
                }
            }

            // Show error message in a more user-friendly way
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
            errorAlert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
            errorAlert.innerHTML = `
                <i class="bx bx-error-circle me-2"></i>
                <strong>Error!</strong><br>
                ${errorMessage}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(errorAlert);

            // Auto-hide after 10 seconds (longer to read error)
            setTimeout(() => {
                if (errorAlert.parentNode) {
                    errorAlert.remove();
                }
            }, 10000);
        } else {
            // If download was successful, just log the error but don't show it to user
            console.warn('Minor error after successful download:', error);
        }
    });
});
</script>
@endsection
