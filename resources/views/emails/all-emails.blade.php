@extends('layouts.app')

@section('title', 'Designers Inbox')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <x-modern-breadcrumb
                title="Designers Inbox"
                subtitle="Monitor emails from engineering@orion-contracting.com"
                icon="bx bx-inbox"
                theme="emails"
                :breadcrumbs="[
                    ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                    ['title' => 'Designers Inbox', 'url' => '#', 'icon' => 'bx bx-inbox']
                ]"
            />
        </div>
    </div>

    <!-- Designers Inbox Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center">
                <i class="bx bx-info-circle me-2"></i>
                <div>
                    <strong>Engineering Inbox:</strong> This section shows emails from <strong>engineering@orion-contracting.com</strong>.
                    Only managers can access this inbox to monitor incoming emails from clients and stakeholders.
                </div>
            </div>
        </div>
    </div>

    <!-- Email Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-envelope text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Total Emails</h6>
                            <h4 class="mb-0">{{ $emailStats['total_messages'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-check text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Stored in DB</h6>
                            <h4 class="mb-0">{{ $storedEmails->total() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-envelope-open text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Unread</h6>
                            <h4 class="mb-0">{{ $storedEmails->where('status', 'received')->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-info rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-paperclip text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">With Attachments</h6>
                            <h4 class="mb-0">{{ $storedEmails->filter(function($email) { return !empty($email->attachments); })->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" onclick="fetchAndStoreEmails()">
                            <i class="bx bx-download me-1"></i>Fetch & Store Emails
                        </button>
                        <button class="btn btn-success" onclick="exportEmails()">
                            <i class="bx bx-export me-1"></i>Export to CSV
                        </button>
                        <button class="btn btn-info" onclick="refreshStats()">
                            <i class="bx bx-refresh me-1"></i>Refresh Stats
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-cog me-1"></i>Bulk Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="bulkAction('mark_read')">Mark Selected as Read</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkAction('mark_unread')">Mark Selected as Unread</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkAction('delete')">Delete Selected</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('emails.all') }}" id="searchForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">From Email</label>
                                <input type="email" class="form-control" name="from" value="{{ $searchCriteria['from'] ?? '' }}" placeholder="sender@example.com">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Email</label>
                                <input type="email" class="form-control" name="to" value="{{ $searchCriteria['to'] ?? '' }}" placeholder="recipient@example.com">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" name="subject" value="{{ $searchCriteria['subject'] ?? '' }}" placeholder="Search in subject">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Domain</label>
                                <input type="text" class="form-control" name="domain" value="{{ $searchCriteria['domain'] ?? '' }}" placeholder="example.com">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="after" value="{{ $searchCriteria['after'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="before" value="{{ $searchCriteria['before'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Limit</label>
                                <select class="form-control" name="limit">
                                    <option value="50" {{ ($searchCriteria['limit'] ?? 50) == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ ($searchCriteria['limit'] ?? 50) == 100 ? 'selected' : '' }}>100</option>
                                    <option value="200" {{ ($searchCriteria['limit'] ?? 50) == 200 ? 'selected' : '' }}>200</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search me-1"></i>Search
                                    </button>
                                    <a href="{{ route('emails.all') }}" class="btn btn-secondary">
                                        <i class="bx bx-x me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Emails Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-envelope me-2"></i>Stored Emails
                    </h5>
                    <div class="d-flex align-items-center">
                        <input type="checkbox" id="selectAll" class="form-check-input me-2" onchange="toggleSelectAll()">
                        <label for="selectAll" class="form-check-label">Select All</label>
                    </div>
                </div>
                <div class="card-body">
                    @if($storedEmails->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllHeader" class="form-check-input" onchange="toggleSelectAll()">
                                        </th>
                                        <th>From</th>
                                        <th>Subject</th>
                                        <th>Received</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($storedEmails as $email)
                                        <tr class="{{ $email->status === 'received' ? 'table-warning' : 'table-light' }}">
                                            <td>
                                                <input type="checkbox" class="form-check-input email-checkbox" value="{{ $email->id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                        <span class="text-white fw-bold" style="font-size: 12px;">{{ substr($email->sender_name, 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold" style="font-size: 14px;">{{ $email->sender_name }}</div>
                                                        <small class="text-muted" style="font-size: 11px;">{{ $email->from_email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold" style="font-size: 14px;">{{ $email->subject }}</div>
                                                <small class="text-muted" style="font-size: 11px;">{{ $email->preview }}</small>
                                            </td>
                                            <td>
                                                <div style="font-size: 12px;">{{ $email->received_at->format('M d, H:i') }}</div>
                                                <small class="text-muted" style="font-size: 10px;">{{ $email->received_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('emails.show', $email) }}" class="btn btn-sm btn-outline-primary" title="View Email" style="padding: 4px 8px;">
                                                        <i class="bx bx-show" style="font-size: 12px;"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEmail({{ $email->id }})" title="Delete Email" style="padding: 4px 8px;">
                                                        <i class="bx bx-trash" style="font-size: 12px;"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $storedEmails->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-envelope-open display-1 text-muted"></i>
                            <h5 class="mt-3">No emails stored yet</h5>
                            <p class="text-muted">Click "Fetch & Store Emails" to import emails from your Gmail account.</p>
                            <button class="btn btn-primary" onclick="fetchAndStoreEmails()">
                                <i class="bx bx-download me-1"></i>Fetch & Store Emails
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Processing...</h5>
                <p class="text-muted">Please wait while we fetch emails from your Gmail account.</p>
            </div>
        </div>
    </div>
</div>

<script>
let selectedEmails = new Set();

function fetchAndStoreEmails() {
    const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
    modal.show();

    fetch('{{ route("auto-emails.fetch") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        modal.hide();
        if (data.success) {
            showAlert('success', `Successfully fetched ${data.data.fetched} emails and stored ${data.data.stored} new emails. Created ${data.data.notifications_created} notifications.`);
            // Update notification count in navigation
            updateNotificationCount();
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAlert('error', data.message || 'Error fetching emails.');
        }
    })
    .catch(error => {
        modal.hide();
        console.error('Error:', error);
        showAlert('error', 'An error occurred while fetching emails.');
    });
}

function exportEmails() {
    window.open('{{ route("emails.export") }}', '_blank');
}

function refreshStats() {
    fetch('{{ route("emails.stats") }}')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Stats refreshed successfully.');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert('error', 'Error refreshing stats.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while refreshing stats.');
    });
}


function deleteEmail(emailId) {
    if (confirm('Are you sure you want to delete this email?')) {
        fetch(`/emails/${emailId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Email deleted successfully.');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('error', 'Error deleting email.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred.');
        });
    }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.email-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        if (selectAll.checked) {
            selectedEmails.add(checkbox.value);
        } else {
            selectedEmails.delete(checkbox.value);
        }
    });
}

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.email-checkbox:checked');
    const emailIds = Array.from(checkboxes).map(cb => cb.value);

    if (emailIds.length === 0) {
        showAlert('warning', 'Please select at least one email.');
        return;
    }

    const actionText = {
        'mark_read': 'mark as read',
        'mark_unread': 'mark as unread',
        'delete': 'delete'
    };

    if (confirm(`Are you sure you want to ${actionText[action]} ${emailIds.length} email(s)?`)) {
        fetch('{{ route("emails.bulk-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                email_ids: emailIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('error', data.message || 'Error performing bulk action.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred during bulk operation.');
        });
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' :
                      type === 'error' ? 'alert-danger' :
                      type === 'warning' ? 'alert-warning' : 'alert-info';

    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="bx bx-${type === 'success' ? 'check-circle' : type === 'error' ? 'error-circle' : type === 'warning' ? 'error' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    // Insert alert at the top of the page
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Handle individual checkbox changes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.email-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedEmails.add(this.value);
            } else {
                selectedEmails.delete(this.value);
            }

            // Update select all checkbox
            const selectAll = document.getElementById('selectAll');
            selectAll.checked = selectedEmails.size === checkboxes.length;
        });
    });

    // Initialize automatic email fetching
    initializeAutoEmailFetch();
});

// Initialize automatic email fetching
function initializeAutoEmailFetch() {
    // Auto-fetch emails every 3 minutes
    setInterval(function() {
        autoFetchEmails();
    }, 3 * 60 * 1000); // 3 minutes

    // Update notification count every 10 seconds
    setInterval(function() {
        updateNotificationCount();
    }, 10 * 1000); // 10 seconds

    // Initial fetch and count update
    updateNotificationCount();
}

// Automatic email fetching function
function autoFetchEmails() {
    fetch('{{ route("auto-emails.fetch") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.stored > 0) {
            console.log(`Auto-fetch: Stored ${data.data.stored} new emails, created ${data.data.notifications_created} notifications`);
            // Update notification count
            updateNotificationCount();
            // Show subtle notification
            showAutoFetchNotification(data.data);
        }
    })
    .catch(error => {
        console.error('Auto-fetch error:', error);
    });
}

// Update notification count in navigation
function updateNotificationCount() {
    fetch('{{ route("auto-emails.unread-count") }}')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('nav-designers-inbox-count');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'block' : 'none';
            }
        }
    })
    .catch(error => {
        console.error('Error updating notification count:', error);
    });
}

// Show auto-fetch notification
function showAutoFetchNotification(data) {
    const message = `Auto-fetch: ${data.stored} new emails stored, ${data.notifications_created} notifications created`;
    showAlert('info', message);
}
</script>
@endsection
