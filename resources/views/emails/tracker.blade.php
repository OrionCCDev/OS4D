@extends('layouts.header')

@section('title', 'Email Tracker - engineering@orion-contracting.com')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bx bx-envelope me-2"></i>
                            Email Tracker - engineering@orion-contracting.com
                        </h5>
                        <small class="text-muted">
                            @if(Auth::user()->isManager())
                                Manager View: All emails
                            @else
                                Your emails: sent, received, replies, and CC'd
                            @endif
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" onclick="exportEmails()">
                            <i class="bx bx-download me-1"></i>Export CSV
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="refreshEmails()">
                            <i class="bx bx-refresh me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="total-emails">{{ $stats['total_emails'] ?? 0 }}</h4>
                                    <small>Total Emails</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="sent-emails">{{ $stats['sent_emails'] ?? 0 }}</h4>
                                    <small>Sent</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="received-emails">{{ $stats['received_emails'] ?? 0 }}</h4>
                                    <small>Received</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="replies">{{ $stats['replies'] ?? 0 }}</h4>
                                    <small>Replies</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="designers-emails">{{ $stats['designers_emails'] ?? 0 }}</h4>
                                    <small>Designers</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-dark text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1" id="emails-today">{{ $stats['emails_today'] ?? 0 }}</h4>
                                    <small>Today</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters and Search -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="search-input" placeholder="Search emails..." value="{{ $search }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="filter-select">
                                <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Emails</option>
                                <option value="sent" {{ $filter === 'sent' ? 'selected' : '' }}>Sent Emails</option>
                                <option value="received" {{ $filter === 'received' ? 'selected' : '' }}>Received Emails</option>
                                <option value="replies" {{ $filter === 'replies' ? 'selected' : '' }}>Replies Only</option>
                                <option value="designers" {{ $filter === 'designers' ? 'selected' : '' }}>Designers Emails</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="bx bx-filter me-1"></i>Filter
                            </button>
                        </div>
                    </div>

                    <!-- Email List -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Subject</th>
                                    <th>Sent By</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="emails-table-body">
                                @forelse($emails as $email)
                                <tr class="email-row" data-email-id="{{ $email->id }}">
                                    <td>
                                        @if($email->reply_to_email_id)
                                            <span class="badge bg-success">
                                                <i class="bx bx-reply me-1"></i>Reply
                                            </span>
                                        @elseif($email->email_type === 'sent')
                                            <span class="badge bg-primary">
                                                <i class="bx bx-send me-1"></i>Sent
                                            </span>
                                        @else
                                            <span class="badge bg-info">
                                                <i class="bx bx-envelope me-1"></i>Received
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <span class="avatar-initial rounded-circle bg-primary">
                                                    {{ strtoupper(substr($email->from_email, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $email->from_email }}</div>
                                                @if(str_contains($email->from_email, 'engineering@orion-contracting.com'))
                                                    <small class="text-success">Designers</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $email->to_email }}</div>
                                        @if($email->cc)
                                            <small class="text-muted">CC: {{ $email->cc }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $email->subject }}</div>
                                        @if($email->body)
                                            <small class="text-muted">{{ Str::limit(strip_tags($email->body), 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($email->user)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <span class="avatar-initial rounded-circle bg-secondary">
                                                        {{ strtoupper(substr($email->user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $email->user->name }}</div>
                                                    <small class="text-muted">{{ $email->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $email->created_at->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $email->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($email->status === 'sent')
                                            <span class="badge bg-success">Sent</span>
                                        @elseif($email->status === 'received')
                                            <span class="badge bg-info">Received</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($email->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="viewEmail({{ $email->id }})">
                                                        <i class="bx bx-show me-2"></i>View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="markAsRead({{ $email->id }})">
                                                        <i class="bx bx-check me-2"></i>Mark as Read
                                                    </a>
                                                </li>
                                                @if($email->task_id)
                                                <li>
                                                    <a class="dropdown-item" href="/tasks/{{ $email->task_id }}">
                                                        <i class="bx bx-task me-2"></i>View Task
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bx bx-envelope-open fs-1 mb-3"></i>
                                            <h6>No emails found</h6>
                                            <p class="mb-0">No emails match your current filters.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <small class="text-muted">
                                Showing {{ $emails->firstItem() ?? 0 }} to {{ $emails->lastItem() ?? 0 }} of {{ $emails->total() }} emails
                            </small>
                        </div>
                        <div>
                            {{ $emails->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Details Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="emailModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 30 seconds
setInterval(function() {
    refreshStats();
}, 30000);

// Load stats on page load
document.addEventListener('DOMContentLoaded', function() {
    refreshStats();
});

function refreshStats() {
    fetch('{{ route("email-tracker.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-emails').textContent = data.stats.total_emails;
                document.getElementById('sent-emails').textContent = data.stats.sent_emails;
                document.getElementById('received-emails').textContent = data.stats.received_emails;
                document.getElementById('replies').textContent = data.stats.replies;
                document.getElementById('designers-emails').textContent = data.stats.designers_emails;
                document.getElementById('emails-today').textContent = data.stats.emails_today;
            }
        })
        .catch(error => console.error('Error refreshing stats:', error));
}

function applyFilters() {
    const search = document.getElementById('search-input').value;
    const filter = document.getElementById('filter-select').value;

    const url = new URL(window.location);
    url.searchParams.set('search', search);
    url.searchParams.set('filter', filter);
    url.searchParams.set('page', '1'); // Reset to first page

    window.location.href = url.toString();
}

function refreshEmails() {
    window.location.reload();
}

function viewEmail(emailId) {
    const modal = new bootstrap.Modal(document.getElementById('emailModal'));
    const modalBody = document.getElementById('emailModalBody');

    modalBody.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    modal.show();

    // Load email details
    fetch(`/emails/${emailId}`)
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error me-2"></i>
                    Error loading email details: ${error.message}
                </div>
            `;
        });
}

function markAsRead(emailId) {
    fetch(`{{ route("email-tracker.mark-read", ":id") }}`.replace(':id', emailId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Email marked as read');
            refreshStats();
        } else {
            showAlert('error', data.message || 'Error marking email as read');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error marking email as read');
    });
}

function exportEmails() {
    const filter = document.getElementById('filter-select').value;
    const url = `{{ route("email-tracker.export") }}?filter=${filter}`;

    // Create a temporary link and click it
    const link = document.createElement('a');
    link.href = url;
    link.download = `emails_${filter}_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    // Insert at top of card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertAdjacentHTML('afterbegin', alertHtml);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = cardBody.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Handle Enter key in search input
document.getElementById('search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>

<style>
.email-row:hover {
    background-color: #f8f9fa;
}

.avatar {
    width: 32px;
    height: 32px;
}

.avatar-xs {
    width: 24px;
    height: 24px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #697a8d;
}

.badge {
    font-size: 0.75em;
}
</style>
@endsection
