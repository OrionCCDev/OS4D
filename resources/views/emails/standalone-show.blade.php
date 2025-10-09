@extends('layouts.app')

@section('title', 'Engineering Inbox Email - ' . Str::limit($email->subject, 50))

@section('head')
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        /* Override layout constraints for full-width email content */
        .layout-page {
            width: 100% !important;
        }

        .content-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
        }

        .email-page {
            width: 100%;
            min-height: 100vh;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .email-container {
            max-width: 100%;
            margin: 0 auto;
        }

        .breadcrumb-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .breadcrumb-section h1 {
            font-size: 24px;
            margin: 0;
            font-weight: 600;
        }

        .breadcrumb-section p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }

        .email-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .email-header h2 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-unread {
            background: #fff3cd;
            color: #856404;
        }

        .status-read {
            background: #d1edff;
            color: #0c5460;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .email-info {
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .email-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .sender-avatar {
            width: 50px;
            height: 50px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .email-details h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #333;
        }

        .email-details .meta-row {
            margin-bottom: 5px;
            font-size: 14px;
        }

        .email-details .meta-row strong {
            color: #666;
        }

        .badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }

        .badge-primary {
            background: #007bff;
            color: white;
        }

        .badge-info {
            background: #17a2b8;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #212529;
        }

        /* FULL WIDTH EMAIL CONTENT */
        .email-content-section {
            width: 100%;
            padding: 0;
            margin: 0;
        }

        .email-content-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            color: #333;
        }

        .email-content-body {
            width: 100%;
            padding: 0;
            margin: 0;
            background: #f8f9fa;
        }

        .email-wrapper {
            width: 100%;
            max-width: 100%;
            padding: 20px;
            background: #f8f9fa;
        }

        .email-content {
            width: 100%;
            max-width: 100%;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
        }

        /* Force all email content to use full width */
        .email-content * {
            max-width: 100% !important;
        }

        .email-content img {
            max-width: 100% !important;
            height: auto !important;
        }

        .email-content table {
            width: 100% !important;
            max-width: 100% !important;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .email-content table td,
        .email-content table th {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .email-content pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            max-width: 100%;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            overflow-x: auto;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .email-page {
                padding: 10px;
            }

            .email-meta {
                flex-direction: column;
                gap: 15px;
            }

            .action-buttons {
                width: 100%;
                justify-content: center;
            }

            .btn {
                flex: 1;
                text-align: center;
            }

            .email-content {
                padding: 20px;
            }
        }

        /* Attachments and replies styling */
        .attachments-section,
        .replies-section {
            padding: 20px;
            border-top: 1px solid #dee2e6;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }

        .attachment-icon {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #6c757d;
        }

        .reply-item {
            border-bottom: 1px solid #dee2e6;
            padding: 20px 0;
        }

        .reply-item:last-child {
            border-bottom: none;
        }

        .reply-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .reply-avatar {
            width: 35px;
            height: 35px;
            background: #6c757d;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin-right: 10px;
        }

        .no-content {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .no-content i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
@endsection

@section('content')
<div class="email-page">
        <div class="email-container">
            <!-- Breadcrumb Section -->
            <div class="breadcrumb-section">
                <h1><i class='bx bx-envelope-open'></i> Engineering Inbox Email</h1>
                <p>View and manage email details from designers inbox</p>
            </div>

            <!-- Main Email Card -->
            <div class="email-card">
                <!-- Email Header -->
                <div class="email-header">
                    <div>
                        <h2><i class='bx bx-file-text'></i> Email Details</h2>
                        @if($email->status === 'read')
                            <span class="status-badge status-read">Read</span>
                        @else
                            <span class="status-badge status-unread">Unread</span>
                        @endif
                    </div>
                    <div class="action-buttons">
                        <a href="{{ route('emails.all') }}" class="btn btn-secondary">
                            <i class='bx bx-arrow-back'></i> Back to Inbox
                        </a>
                        @if($email->status === 'received')
                            <button class="btn btn-success" onclick="markAsRead({{ $email->id }})">
                                <i class='bx bx-check'></i> Mark as Read
                            </button>
                        @else
                            <button class="btn btn-warning" onclick="markAsUnread({{ $email->id }})">
                                <i class='bx bx-envelope'></i> Mark as Unread
                            </button>
                        @endif
                        <button class="btn btn-danger" onclick="deleteEmail({{ $email->id }})">
                            <i class='bx bx-trash'></i> Delete
                        </button>
                    </div>
                </div>

                <!-- Email Info -->
                <div class="email-info">
                    <div class="email-meta">
                        <div class="sender-avatar">
                            {{ substr($email->sender_name, 0, 1) }}
                        </div>
                        <div class="email-details">
                            <h3>{{ $email->subject }}</h3>
                            <div class="meta-row">
                                <strong>From:</strong> {{ $email->from_email }}
                                <span class="badge badge-primary">Engineering Inbox</span>
                            </div>
                            <div class="meta-row">
                                <strong>To:</strong> {{ $email->to_email }}
                            </div>
                            @if(!empty($email->cc_emails))
                            <div class="meta-row">
                                <strong>CC:</strong> {{ implode(', ', $email->cc_emails) }}
                            </div>
                            @endif
                            <div class="meta-row">
                                <strong>Received:</strong>
                                @if($email->received_at)
                                    {{ $email->received_at->format('F d, Y \a\t H:i') }}
                                    <small>({{ $email->received_at->diffForHumans() }})</small>
                                @else
                                    Unknown
                                @endif
                            </div>
                            @if(!empty($email->attachments))
                            <div class="meta-row">
                                <span class="badge badge-warning">
                                    <i class='bx bx-paperclip'></i> {{ count($email->attachments) }} Attachment(s)
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Email Content Section - FULL WIDTH -->
                <div class="email-content-section">
                    <div class="email-content-header">
                        <i class='bx bx-file-text'></i> Email Content
                    </div>
                    <div class="email-content-body">
                        @php
                            // Get the email body
                            $emailBody = $email->body;

                            // First, try PHP's built-in quoted-printable decode
                            $decodedBody = quoted_printable_decode($emailBody);

                            // If that didn't work or we still have encoding artifacts, do manual decoding
                            if (strpos($decodedBody, '=3D') !== false || strpos($decodedBody, '=20') !== false || strpos($decodedBody, '=3C') !== false) {
                                $replacements = [
                                    '=3D' => '=', '=20' => ' ', '=0A' => "\n", '=0D' => "\r",
                                    '=3C' => '<', '=3E' => '>', '=22' => '"', '=27' => "'",
                                    '=2C' => ',', '=2E' => '.', '=2F' => '/', '=3A' => ':',
                                    '=3B' => ';', '=40' => '@', '=5B' => '[', '=5D' => ']',
                                    '=5F' => '_', '=60' => '`', '=7B' => '{', '=7D' => '}',
                                    '=7E' => '~', '=09' => "\t", '=28' => '(', '=29' => ')',
                                    '=2B' => '+', '=2D' => '-', '=3F' => '?', '=21' => '!',
                                    '=23' => '#', '=24' => '$', '=25' => '%', '=26' => '&',
                                    '=2A' => '*',
                                ];
                                $decodedBody = str_replace(array_keys($replacements), array_values($replacements), $emailBody);
                                $decodedBody = preg_replace('/=\r?\n/', '', $decodedBody);
                                $decodedBody = preg_replace('/=\s*$/', '', $decodedBody);
                            }

                            // Use parsed body if available, otherwise use decoded body
                            $displayBody = ($parsedBody && strlen($parsedBody) > 10) ? $parsedBody : $decodedBody;

                            // Fix any character encoding issues
                            $displayBody = mb_convert_encoding($displayBody, 'UTF-8', 'UTF-8');
                        @endphp

                        @if($displayBody && strlen($displayBody) > 10)
                            <div class="email-wrapper">
                                <div class="email-content">
                                    {!! $displayBody !!}
                                </div>
                            </div>
                        @else
                            <div class="no-content">
                                <i class='bx bx-file-blank'></i>
                                <p>No content available</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Attachments Section -->
                @if(!empty($email->attachments))
                <div class="attachments-section">
                    <h4><i class='bx bx-paperclip'></i> Attachments ({{ count($email->attachments) }})</h4>
                    @foreach($email->attachments as $attachment)
                    <div class="attachment-item">
                        <div class="attachment-icon">
                            <i class='bx bx-file'></i>
                        </div>
                        <div>
                            <div style="font-weight: 600;">{{ $attachment['filename'] ?? 'Unknown File' }}</div>
                            <small style="color: #6c757d;">
                                {{ $attachment['mime_type'] ?? 'Unknown Type' }}
                                @if(isset($attachment['size']))
                                    • {{ number_format($attachment['size'] / 1024, 1) }} KB
                                @endif
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Replies Section -->
                @if($email->replies && $email->replies->count() > 0)
                <div class="replies-section">
                    <h4><i class='bx bx-reply'></i> Replies ({{ $email->replies->count() }})</h4>
                    @foreach($email->replies as $reply)
                    <div class="reply-item">
                        <div class="reply-header">
                            <div class="reply-avatar">
                                {{ substr($reply->sender_name, 0, 1) }}
                            </div>
                            <div>
                                <strong>{{ $reply->from_email }}</strong>
                                <small style="color: #6c757d; margin-left: 10px;">
                                    @if($reply->received_at)
                                        {{ $reply->received_at->format('M d, Y H:i') }}
                                    @else
                                        Unknown date
                                    @endif
                                </small>
                                <span class="badge badge-info" style="margin-left: 10px;">Reply</span>
                            </div>
                        </div>
                        @php
                            $replyContent = $reply->body;
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
                            }
                            $displayReplyContent = $parsedReplies[$reply->id] ?? $replyContent;
                        @endphp
                        <div class="email-wrapper">
                            <div class="email-content">
                                {!! $displayReplyContent !!}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

@section('scripts')
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
                    setTimeout(() => window.location.reload(), 1000);
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
                    setTimeout(() => window.location.reload(), 1000);
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
                        setTimeout(() => window.location.href = '{{ route("emails.all") }}', 1000);
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
            const icon = type === 'success' ? 'check-circle' : 'error-circle';
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <i class="bx bx-${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', alertHtml);
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) alert.remove();
            }, 5000);
        }
    </script>
@endsection
