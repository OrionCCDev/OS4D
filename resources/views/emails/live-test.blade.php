@extends('layouts.header')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">🚀 Live Email Reply Testing</h3>
                    <p class="mb-0">Test email reply notifications immediately</p>
                </div>
                <div class="card-body">
                    <!-- Current Status -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>📊 Current Status</h5>
                            <div id="current-status" class="alert alert-info">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6>⚡ Quick Test</h6>
                                </div>
                                <div class="card-body">
                                    <p>Create a test reply notification immediately:</p>
                                    <button class="btn btn-success btn-block" onclick="createTestReply()">
                                        <i class="fas fa-paper-plane"></i> Create Test Reply Notification
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6>📧 Designers Reply</h6>
                                </div>
                                <div class="card-body">
                                    <p>Simulate reply from designers@orion-contracting.com:</p>
                                    <button class="btn btn-primary btn-block" onclick="simulateDesignersReply()">
                                        <i class="fas fa-reply"></i> Simulate Designers Reply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Emails -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>📨 Recent Sent Emails</h5>
                            <div id="recent-emails">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Notifications -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>🔔 Recent Notifications</h5>
                            <div id="recent-notifications">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Results -->
                    <div class="row">
                        <div class="col-12">
                            <h5>📋 Test Results</h5>
                            <div id="test-results" class="alert" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCurrentStatus();
});

function loadCurrentStatus() {
    fetch('/live/notification-status')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update status
            const statusDiv = document.getElementById('current-status');
            statusDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary">${data.stats.total_notifications}</h4>
                            <small>Total Notifications</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning">${data.stats.unread_notifications}</h4>
                            <small>Unread Notifications</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success">${data.stats.reply_notifications}</h4>
                            <small>Reply Notifications</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">${data.recent_emails.length}</h4>
                            <small>Recent Emails</small>
                        </div>
                    </div>
                </div>
            `;

            // Update recent emails
            const emailsDiv = document.getElementById('recent-emails');
            if (data.recent_emails.length === 0) {
                emailsDiv.innerHTML = '<div class="alert alert-warning">No recent emails found. Send an email first.</div>';
            } else {
                let emailsHtml = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>ID</th><th>Subject</th><th>Sent</th><th>Replied</th><th>Actions</th></tr></thead><tbody>';

                data.recent_emails.forEach(email => {
                    emailsHtml += `
                        <tr>
                            <td>${email.id}</td>
                            <td>${email.subject}</td>
                            <td>${new Date(email.sent_at).toLocaleString()}</td>
                            <td>${email.replied_at ? '✅ ' + new Date(email.replied_at).toLocaleString() : '❌ No'}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="simulateDesignersReplyForEmail(${email.id})">
                                    Test Reply
                                </button>
                            </td>
                        </tr>
                    `;
                });

                emailsHtml += '</tbody></table></div>';
                emailsDiv.innerHTML = emailsHtml;
            }

            // Update recent notifications
            const notificationsDiv = document.getElementById('recent-notifications');
            if (data.recent_notifications.length === 0) {
                notificationsDiv.innerHTML = '<div class="alert alert-info">No notifications yet. Create a test reply!</div>';
            } else {
                let notificationsHtml = '<div class="list-group">';

                data.recent_notifications.forEach(notification => {
                    const isRead = notification.is_read ? 'read' : 'unread';
                    const badgeClass = notification.is_read ? 'badge-secondary' : 'badge-primary';

                    notificationsHtml += `
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${notification.message}</h6>
                                <span class="badge ${badgeClass}">${isRead}</span>
                            </div>
                            <p class="mb-1">Type: ${notification.notification_type}</p>
                            <small>Created: ${new Date(notification.created_at).toLocaleString()}</small>
                        </div>
                    `;
                });

                notificationsHtml += '</div>';
                notificationsDiv.innerHTML = notificationsHtml;
            }
        } else {
            document.getElementById('current-status').innerHTML = '<div class="alert alert-danger">Error loading status: ' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Error loading status:', error);
        document.getElementById('current-status').innerHTML = '<div class="alert alert-danger">Error loading status</div>';
    });
}

function createTestReply() {
    const resultsDiv = document.getElementById('test-results');
    resultsDiv.className = 'alert alert-info';
    resultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><br>Creating test reply...</div>';
    resultsDiv.style.display = 'block';

    fetch('/live/test-reply', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            resultsDiv.className = 'alert alert-success';
            resultsDiv.innerHTML = `
                <h6><i class="fas fa-check-circle"></i> Test Reply Created Successfully!</h6>
                <p><strong>Original Email ID:</strong> ${data.data.original_email_id}</p>
                <p><strong>Reply Email ID:</strong> ${data.data.reply_email_id}</p>
                <p><strong>Notification ID:</strong> ${data.data.notification_id}</p>
                <p><strong>User ID:</strong> ${data.data.user_id}</p>
                <p><strong>Subject:</strong> ${data.data.original_subject}</p>
                <p><em>Check your notifications now!</em></p>
            `;
        } else {
            resultsDiv.className = 'alert alert-danger';
            resultsDiv.innerHTML = `
                <h6><i class="fas fa-exclamation-circle"></i> Test Reply Failed</h6>
                <p><strong>Error:</strong> ${data.message}</p>
            `;
        }

        // Refresh status
        setTimeout(() => {
            loadCurrentStatus();
        }, 1000);
    })
    .catch(error => {
        console.error('Error creating test reply:', error);
        resultsDiv.className = 'alert alert-danger';
        resultsDiv.innerHTML = `
            <h6><i class="fas fa-exclamation-circle"></i> Test Reply Failed</h6>
            <p><strong>Error:</strong> ${error.message}</p>
        `;
    });
}

function simulateDesignersReply() {
    // Get the first recent email
    fetch('/live/notification-status')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.recent_emails.length > 0) {
            simulateDesignersReplyForEmail(data.recent_emails[0].id);
        } else {
            alert('No recent emails found. Send an email first.');
        }
    });
}

function simulateDesignersReplyForEmail(emailId) {
    const resultsDiv = document.getElementById('test-results');
    resultsDiv.className = 'alert alert-info';
    resultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><br>Simulating designers reply...</div>';
    resultsDiv.style.display = 'block';

    fetch('/live/simulate-designers-reply', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email_id: emailId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            resultsDiv.className = 'alert alert-success';
            resultsDiv.innerHTML = `
                <h6><i class="fas fa-check-circle"></i> Designers Reply Simulated Successfully!</h6>
                <p><strong>Original Email ID:</strong> ${data.data.original_email_id}</p>
                <p><strong>Reply Email ID:</strong> ${data.data.reply_email_id}</p>
                <p><strong>Notification ID:</strong> ${data.data.notification_id}</p>
                <p><strong>User ID:</strong> ${data.data.user_id}</p>
                <p><strong>Subject:</strong> ${data.data.original_subject}</p>
                <p><em>This simulates a reply from designers@orion-contracting.com!</em></p>
            `;
        } else {
            resultsDiv.className = 'alert alert-danger';
            resultsDiv.innerHTML = `
                <h6><i class="fas fa-exclamation-circle"></i> Designers Reply Failed</h6>
                <p><strong>Error:</strong> ${data.message}</p>
            `;
        }

        // Refresh status
        setTimeout(() => {
            loadCurrentStatus();
        }, 1000);
    })
    .catch(error => {
        console.error('Error simulating designers reply:', error);
        resultsDiv.className = 'alert alert-danger';
        resultsDiv.innerHTML = `
            <h6><i class="fas fa-exclamation-circle"></i> Designers Reply Failed</h6>
            <p><strong>Error:</strong> ${error.message}</p>
        `;
    });
}
</script>
@endsection
