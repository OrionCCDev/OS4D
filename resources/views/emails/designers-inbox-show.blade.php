@extends('layouts.app')

@section('title', 'Engineering Inbox Email')



@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <x-modern-breadcrumb
                title="Engineering Inbox Email"
                subtitle="View and manage email details from designers inbox"
                icon="bx-envelope-open"
                theme="emails"
                :breadcrumbs="[
                    ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx-home'],
                    ['title' => 'Inbox', 'url' => route('emails.all'), 'icon' => 'bx-inbox'],
                    ['title' => 'Details', 'url' => '#', 'icon' => 'bx-envelope']
                ]"
            />
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-envelope-open me-2"></i>
                        Email Details
                        @if($email->status === 'read')
                            <span class="badge bg-success ms-2">Read</span>
                        @else
                            <span class="badge bg-warning ms-2">Unread</span>
                        @endif
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('emails.all') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i>Back to Inbox
                        </a>
                        @if($email->status === 'received')
                            <button class="btn btn-success btn-sm" onclick="markAsRead({{ $email->id }})">
                                <i class="bx bx-check me-1"></i>Mark as Read
                            </button>
                        @else
                            <button class="btn btn-warning btn-sm" onclick="markAsUnread({{ $email->id }})">
                                <i class="bx bx-envelope me-1"></i>Mark as Unread
                            </button>
                        @endif
                        <button class="btn btn-danger btn-sm" onclick="deleteEmail({{ $email->id }})">
                            <i class="bx bx-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Email Header -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                    <span class="text-white fw-bold fs-5">{{ substr($email->sender_name, 0, 1) }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="mb-1">{{ $email->subject }}</h4>
                                    <div class="text-muted">
                                        <div class="mb-1">
                                            <strong>From:</strong> {{ $email->from_email }}
                                                <span class="badge bg-primary ms-2">Engineering Inbox</span>
                                        </div>
                                        <div class="mb-1">
                                            <strong>To:</strong> {{ $email->to_email }}
                                        </div>
                                        @if(!empty($email->cc_emails))
                                        <div class="mb-1">
                                            <strong>CC:</strong> {{ implode(', ', $email->cc_emails) }}
                                        </div>
                                        @endif
                                        <div class="mb-1">
                                            <strong>Received:</strong>
                                            @if($email->received_at)
                                                {{ $email->received_at->format('F d, Y \a\t H:i') }}
                                                <small class="text-muted ms-2">({{ $email->received_at->diffForHumans() }})</small>
                                            @else
                                                Unknown
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                @if($email->reply_to_email_id)
                                    <span class="badge bg-info fs-6 mb-2">
                                        <i class="bx bx-reply me-1"></i>Reply
                                    </span>
                                @else
                                    <span class="badge bg-primary fs-6 mb-2">
                                        <i class="bx bx-envelope me-1"></i>Received
                                    </span>
                                @endif

                                @if(!empty($email->attachments))
                                    <span class="badge bg-warning fs-6 mb-2">
                                        <i class="bx bx-paperclip me-1"></i>{{ count($email->attachments) }} Attachment(s)
                                    </span>
                                @endif

                                <div class="text-muted small mt-2">
                                    <div><strong>Source:</strong> engineering@orion-contracting.com</div>
                                    <div><strong>Message ID:</strong> {{ $email->message_id }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Body -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bx bx-file-text me-2"></i>Email Content
                            </h6>
                        </div>
                        <div class="card-body">
                            @php
                                // Get the email body
                                $emailBody = $email->body;

                                // First, try PHP's built-in quoted-printable decode
                                $decodedBody = quoted_printable_decode($emailBody);

                                // If that didn't work or we still have encoding artifacts, do manual decoding
                                if (strpos($decodedBody, '=3D') !== false || strpos($decodedBody, '=20') !== false || strpos($decodedBody, '=3C') !== false) {
                                    // Comprehensive quoted-printable decoding
                                    $replacements = [
                                        '=3D' => '=',     // equals sign
                                        '=20' => ' ',     // space
                                        '=0A' => "\n",    // line feed
                                        '=0D' => "\r",    // carriage return
                                        '=3C' => '<',     // less than
                                        '=3E' => '>',     // greater than
                                        '=22' => '"',     // double quote
                                        '=27' => "'",     // single quote
                                        '=2C' => ',',     // comma
                                        '=2E' => '.',     // period
                                        '=2F' => '/',     // forward slash
                                        '=3A' => ':',     // colon
                                        '=3B' => ';',     // semicolon
                                        '=40' => '@',     // at symbol
                                        '=5B' => '[',     // left bracket
                                        '=5D' => ']',     // right bracket
                                        '=5F' => '_',     // underscore
                                        '=60' => '`',     // backtick
                                        '=7B' => '{',     // left brace
                                        '=7D' => '}',     // right brace
                                        '=7E' => '~',     // tilde
                                        '=09' => "\t",    // tab
                                        '=28' => '(',     // left parenthesis
                                        '=29' => ')',     // right parenthesis
                                        '=2B' => '+',     // plus
                                        '=2D' => '-',     // minus
                                        '=3F' => '?',     // question mark
                                        '=21' => '!',     // exclamation
                                        '=23' => '#',     // hash
                                        '=24' => '$',     // dollar
                                        '=25' => '%',     // percent
                                        '=26' => '&',     // ampersand
                                        '=2A' => '*',     // asterisk
                                    ];

                                    // Apply all replacements
                                    $decodedBody = str_replace(array_keys($replacements), array_values($replacements), $emailBody);

                                    // Remove soft line breaks (= at end of lines)
                                    $decodedBody = preg_replace('/=\r?\n/', '', $decodedBody);
                                    $decodedBody = preg_replace('/=\s*$/', '', $decodedBody);

                                    // Clean up multiple spaces
                                    $decodedBody = preg_replace('/\s{2,}/', ' ', $decodedBody);

                                    // Fix broken words that were split across lines
                                    $decodedBody = preg_replace('/([a-zA-Z])\s+([a-zA-Z])/', '$1$2', $decodedBody);
                                }

                                // Use parsed body if available, otherwise use decoded body
                                $displayBody = ($parsedBody && strlen($parsedBody) > 10) ? $parsedBody : $decodedBody;

                                // Fix any character encoding issues
                                $displayBody = mb_convert_encoding($displayBody, 'UTF-8', 'UTF-8');

                                // Clean up any remaining character issues
                                $displayBody = str_replace(['', '=', '=20', '=3D'], ['', '=', ' ', '='], $displayBody);
                            @endphp

                            @if($displayBody && strlen($displayBody) > 10)
                                <div class="email-content-container custom-email-style">
                                    @if(!str_contains($displayBody, '<!DOCTYPE html>'))
                                        <!DOCTYPE html>
                                        <html lang="en">
                                    @endif
                                    {!! $displayBody !!}
                                    @if(!str_contains($displayBody, '</html>'))
                                        </html>
                                    @endif
                                </div>

                                <!-- Debug Info (remove in production) -->
                                <div class="mt-3 p-3 bg-light border rounded">
                                    <small class="text-muted">
                                        <strong>Debug Info:</strong><br>
                                        Original Body Length: {{ strlen($email->body) }} chars<br>
                                        Decoded Body Length: {{ strlen($displayBody) }} chars<br>
                                        Has DOCTYPE: {{ str_contains($displayBody, '<!DOCTYPE html>') ? 'Yes' : 'No' }}<br>
                                        Has HTML tag: {{ str_contains($displayBody, '<html') ? 'Yes' : 'No' }}<br>
                                        Has encoding artifacts: {{ (strpos($displayBody, '=3D') !== false || strpos($displayBody, '=20') !== false) ? 'Yes' : 'No' }}<br>
                                        Has broken characters: {{ strpos($displayBody, '') !== false ? 'Yes' : 'No' }}<br>
                                        First 200 chars: {{ htmlspecialchars(substr($displayBody, 0, 200)) }}...
                                    </small>
                                </div>
                            @else
                                <div class="text-muted text-center py-4">
                                    <i class="bx bx-file-blank fs-1 mb-3"></i>
                                    <p>No content available</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Attachments Section -->
                    @if(!empty($email->attachments))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bx bx-paperclip me-2"></i>
                                Attachments ({{ count($email->attachments) }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($email->attachments as $attachment)
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="bx bx-file text-muted"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $attachment['filename'] ?? 'Unknown File' }}</div>
                                            <small class="text-muted">
                                                {{ $attachment['mime_type'] ?? 'Unknown Type' }}
                                                @if(isset($attachment['size']))
                                                    • {{ number_format($attachment['size'] / 1024, 1) }} KB
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Replies Section -->
                    @if($email->replies && $email->replies->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bx bx-reply me-2"></i>
                                Replies ({{ $email->replies->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($email->replies as $reply)
                            <div class="reply-item border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-sm bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <span class="text-white fw-bold">{{ substr($reply->sender_name, 0, 1) }}</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong>{{ $reply->from_email }}</strong>
                                                <small class="text-muted ms-2">
                                                    @if($reply->received_at)
                                                        {{ $reply->received_at->format('M d, Y H:i') }}
                                                    @else
                                                        Unknown date
                                                    @endif
                                                </small>
                                            </div>
                                            <span class="badge bg-info">Reply</span>
                                        </div>
                                        @php
                                            // Decode reply content
                                            $replyContent = $reply->body;

                                            // Handle quoted-printable decoding for replies
                                            if (strpos($replyContent, '=3D') !== false || strpos($replyContent, '=20') !== false) {
                                                $replacements = [
                                                    '=3D' => '=', '=20' => ' ', '=0A' => "\n", '=0D' => "\r",
                                                    '=3C' => '<', '=3E' => '>', '=22' => '"', '=27' => "'",
                                                    '=2C' => ',', '=2E' => '.', '=2F' => '/', '=3A' => ':',
                                                    '=3B' => ';', '=40' => '@', '=5B' => '[', '=5D' => ']',
                                                    '=5F' => '_', '=60' => '`', '=7B' => '{', '=7D' => '}',
                                                    '=7E' => '~',
                                                ];
                                                $replyContent = str_replace(array_keys($replacements), array_values($replacements), $replyContent);
                                                $replyContent = preg_replace('/=\r?\n/', '', $replyContent);
                                                $replyContent = preg_replace('/=\s*$/', '', $replyContent);
                                                $replyContent = preg_replace('/\s{2,}/', ' ', $replyContent);
                                            }

                                            $displayReplyContent = $parsedReplies[$reply->id] ?? $replyContent;
                                        @endphp
                                        <div class="email-content-container custom-email-style">
                                            {!! $displayReplyContent !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function markAsRead(emailId) {
        fetch(`/emails/${emailId}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Email marked as read');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('error', data.message || 'Error marking email as read');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred');
        });
    }

    function markAsUnread(emailId) {
        fetch(`/emails/${emailId}/mark-unread`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Email marked as unread');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert('error', data.message || 'Error marking email as unread');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred');
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
                    showAlert('success', 'Email deleted successfully');
                    setTimeout(() => {
                        window.location.href = '{{ route("emails.all") }}';
                    }, 1000);
                } else {
                    showAlert('error', data.message || 'Error deleting email');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred');
            });
        }
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bx bx-${type === 'success' ? 'check-circle' : 'error-circle'} me-2"></i>
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
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}
</style>
@endsection
