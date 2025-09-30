@extends('layouts.header')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🧪 Email Reply Testing</h3>
                </div>
                <div class="card-body">
                    <!-- Recent Emails -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Recent Sent Emails</h5>
                            <div id="recent-emails">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Reply -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Test Email Reply</h5>
                            <div class="card">
                                <div class="card-body">
                                    <form id="test-reply-form">
                                        <div class="form-group">
                                            <label for="email-id">Select Email to Reply To:</label>
                                            <select class="form-control" id="email-id" name="email_id" required>
                                                <option value="">Loading emails...</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="reply-from">Reply From:</label>
                                            <input type="email" class="form-control" id="reply-from" name="from" value="test-reply@example.com" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="reply-subject">Reply Subject:</label>
                                            <input type="text" class="form-control" id="reply-subject" name="subject" placeholder="Re: [Original Subject]" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="reply-body">Reply Body:</label>
                                            <textarea class="form-control" id="reply-body" name="body" rows="3" required>This is a test reply generated at {{ now()->toISOString() }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Send Test Reply
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Results -->
                    <div class="row">
                        <div class="col-12">
                            <h5>Test Results</h5>
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
    loadRecentEmails();

    document.getElementById('test-reply-form').addEventListener('submit', function(e) {
        e.preventDefault();
        sendTestReply();
    });
});

function loadRecentEmails() {
    fetch('/webhook/email/recent-emails')
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const emailsContainer = document.getElementById('recent-emails');
            const emailSelect = document.getElementById('email-id');

            if (data.emails.length === 0) {
                emailsContainer.innerHTML = '<div class="alert alert-info">No recent emails found.</div>';
                emailSelect.innerHTML = '<option value="">No emails available</option>';
                return;
            }

            // Display recent emails
            let emailsHtml = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>ID</th><th>Subject</th><th>From</th><th>To</th><th>Sent</th><th>Actions</th></tr></thead><tbody>';

            // Populate select dropdown
            emailSelect.innerHTML = '';

            data.emails.forEach(email => {
                emailsHtml += `
                    <tr>
                        <td>${email.id}</td>
                        <td>${email.subject}</td>
                        <td>${email.from_email}</td>
                        <td>${email.to_email}</td>
                        <td>${new Date(email.sent_at).toLocaleString()}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="testReplyForEmail(${email.id})">
                                Test Reply
                            </button>
                        </td>
                    </tr>
                `;

                emailSelect.innerHTML += `<option value="${email.id}">ID: ${email.id} - ${email.subject}</option>`;
            });

            emailsHtml += '</tbody></table></div>';
            emailsContainer.innerHTML = emailsHtml;

            // Auto-fill reply subject for first email
            if (data.emails.length > 0) {
                document.getElementById('reply-subject').value = 'Re: ' + data.emails[0].subject;
            }
        } else {
            document.getElementById('recent-emails').innerHTML = '<div class="alert alert-danger">Error loading emails: ' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Error loading recent emails:', error);
        document.getElementById('recent-emails').innerHTML = '<div class="alert alert-danger">Error loading emails</div>';
    });
}

function testReplyForEmail(emailId) {
    document.getElementById('email-id').value = emailId;
    sendTestReply();
}

function sendTestReply() {
    const formData = new FormData(document.getElementById('test-reply-form'));
    const data = Object.fromEntries(formData);

    fetch('/webhook/email/test-reply', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        const resultsDiv = document.getElementById('test-results');

        if (data.status === 'success') {
            resultsDiv.className = 'alert alert-success';
            resultsDiv.innerHTML = `
                <h6><i class="fas fa-check-circle"></i> Test Reply Sent Successfully!</h6>
                <p><strong>Email ID:</strong> ${data.email_id}</p>
                <p><strong>Reply From:</strong> ${data.reply_data.from}</p>
                <p><strong>Reply Subject:</strong> ${data.reply_data.subject}</p>
                <p><strong>Message:</strong> ${data.message}</p>
                <p><em>Check your email notifications to see the reply!</em></p>
            `;
        } else {
            resultsDiv.className = 'alert alert-danger';
            resultsDiv.innerHTML = `
                <h6><i class="fas fa-exclamation-circle"></i> Test Reply Failed</h6>
                <p><strong>Error:</strong> ${data.message}</p>
            `;
        }

        resultsDiv.style.display = 'block';

        // Refresh recent emails
        setTimeout(() => {
            loadRecentEmails();
        }, 1000);
    })
    .catch(error => {
        console.error('Error sending test reply:', error);
        const resultsDiv = document.getElementById('test-results');
        resultsDiv.className = 'alert alert-danger';
        resultsDiv.innerHTML = `
            <h6><i class="fas fa-exclamation-circle"></i> Test Reply Failed</h6>
            <p><strong>Error:</strong> ${error.message}</p>
        `;
        resultsDiv.style.display = 'block';
    });
}
</script>
@endsection
