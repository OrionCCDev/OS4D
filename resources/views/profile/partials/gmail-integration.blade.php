<div class="gmail-integration">
    @if(auth()->user()->hasGmailConnected())
        <div class="alert alert-success d-flex align-items-center mb-3">
            <i class="bx bx-check-circle me-2"></i>
            <div>
                <strong>Gmail Connected!</strong><br>
                <small>Connected on {{ auth()->user()->gmail_connected_at->format('M j, Y \a\t g:i A') }}</small>
            </div>
        </div>

        <p class="text-muted mb-3">
            Your Gmail account is connected and you can send emails from your own Gmail account when tasks are approved.
        </p>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger" onclick="disconnectGmail()">
                <i class="bx bx-unlink me-1"></i>Disconnect Gmail
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="checkGmailStatus()">
                <i class="bx bx-refresh me-1"></i>Refresh Status
            </button>
        </div>
    @else
        <div class="alert alert-info d-flex align-items-center mb-3">
            <i class="bx bx-info-circle me-2"></i>
            <div>
                <strong>Gmail Not Connected</strong><br>
                <small>Connect your Gmail account to send emails from your own Gmail when tasks are approved.</small>
            </div>
        </div>

        <p class="text-muted mb-3">
            By connecting your Gmail account, you'll be able to send task confirmation emails directly from your Gmail account instead of using the system's SMTP server.
        </p>

        <div class="d-flex gap-2">
            <a href="{{ route('gmail.redirect') }}" class="btn btn-primary">
                <i class="bx bx-envelope me-1"></i>Connect Gmail
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="checkGmailStatus()">
                <i class="bx bx-refresh me-1"></i>Check Status
            </button>
        </div>
    @endif
</div>

<script>
function disconnectGmail() {
    if (confirm('Are you sure you want to disconnect your Gmail account? You will no longer be able to send emails from your Gmail account.')) {
        fetch('{{ route("gmail.disconnect") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to disconnect Gmail: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while disconnecting Gmail');
        });
    }
}

function checkGmailStatus() {
    fetch('{{ route("gmail.status") }}')
        .then(response => response.json())
        .then(data => {
            if (data.connected) {
                location.reload();
            } else {
                alert('Gmail is not connected');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while checking Gmail status');
        });
}
</script>
