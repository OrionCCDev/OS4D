<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Engineering Inbox Email - {{ $email->subject }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .email-page {
            width: 100%;
            min-height: 100vh;
            padding: 20px;
        }

        .email-container {
            max-width: 100%;
            margin: 0 auto;
        }

        /* Navigation Bar Styles */
        .navigation-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .nav-left {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-right h1 {
            font-size: 24px;
            margin: 0;
            font-weight: 600;
            color: white;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }

        .nav-btn i {
            font-size: 16px;
        }

        .nav-btn span {
            font-size: 13px;
        }

        /* Special styling for different button types */
        .home-btn:hover {
            background: rgba(76, 175, 80, 0.3);
        }

        .back-btn:hover {
            background: rgba(33, 150, 243, 0.3);
        }

        .prev-btn:hover {
            background: rgba(255, 152, 0, 0.3);
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

            .nav-content {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .nav-left {
                justify-content: center;
                flex-wrap: wrap;
            }

            .nav-right {
                text-align: center;
            }

            .nav-right h1 {
                font-size: 20px;
            }

            .nav-btn {
                flex: 1;
                min-width: 120px;
                justify-content: center;
            }

            .nav-btn span {
                display: none;
            }

            .nav-btn i {
                font-size: 18px;
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

        @media (max-width: 480px) {
            .nav-btn {
                min-width: 80px;
                padding: 8px 12px;
            }

            .nav-btn i {
                font-size: 16px;
            }

            .nav-right h1 {
                font-size: 18px;
            }
        }

        /* Attachments and replies styling */
        .attachments-section,
        .replies-section {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
        }

        .attachments-section h4,
        .replies-section h4 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .attachments-grid {
            display: grid;
            gap: 15px;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .attachment-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #007bff;
        }

        .attachment-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 24px;
            flex-shrink: 0;
        }

        .attachment-details {
            flex: 1;
            min-width: 0;
        }

        .attachment-name {
            font-weight: 600;
            color: #495057;
            margin-bottom: 4px;
            word-break: break-word;
        }

        .attachment-meta {
            font-size: 12px;
            color: #6c757d;
        }

        .attachment-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .attachment-actions .btn {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .attachment-actions .btn:hover {
            transform: translateY(-1px);
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
</head>
<body>
    <div class="email-page">
        <div class="email-container">
            <!-- Navigation Bar -->
            <div class="navigation-bar">
                <div class="nav-content">
                    <div class="nav-left">
                        <a href="{{ route('dashboard') }}" class="nav-btn home-btn">
                            <i class='bx bx-home'></i>
                            <span>Home</span>
                        </a>
                        <a href="{{ route('emails.all') }}" class="nav-btn back-btn">
                            <i class='bx bx-arrow-back'></i>
                            <span>Back to Inbox</span>
                        </a>
                        <button onclick="window.history.back()" class="nav-btn prev-btn">
                            <i class='bx bx-left-arrow-alt'></i>
                            <span>Previous Page</span>
                        </button>
                    </div>
                    <div class="nav-right">
                        <h1><i class='bx bx-envelope-open'></i> Engineering Inbox Email</h1>
                    </div>
                </div>
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
                            @if(!empty($email->cc_emails) && is_array($email->cc_emails))
                            <div class="meta-row">
                                <strong>CC:</strong> {{ implode(', ', array_filter($email->cc_emails)) }}
                            </div>
                            @elseif(!empty($email->cc))
                            <div class="meta-row">
                                <strong>CC:</strong> {{ $email->cc }}
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

                            // Apply comprehensive character encoding fix
                            $displayBody = \App\Services\DesignersInboxEmailService::fixCharacterEncodingStatic($displayBody);
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
                @php
                    // Check for attachments in email record or task email preparation
                    $attachments = $email->attachments ?? [];

                    // If this is a sent email and no attachments in email record, check task email preparation
                    if (empty($attachments) && $email->task_id) {
                        $taskEmailPrep = \App\Models\TaskEmailPreparation::where('task_id', $email->task_id)
                            ->where('status', 'sent')
                            ->first();
                        if ($taskEmailPrep && !empty($taskEmailPrep->attachments)) {
                            // Convert file paths to attachment format
                            $attachments = [];
                            foreach ($taskEmailPrep->attachments as $index => $filePath) {
                                $fullPath = storage_path('app/' . $filePath);
                                if (file_exists($fullPath)) {
                                    $attachments[] = [
                                        'filename' => basename($filePath),
                                        'mime_type' => mime_content_type($fullPath) ?: 'application/octet-stream',
                                        'size' => filesize($fullPath),
                                        'file_path' => $filePath,
                                        'source' => 'task_preparation'
                                    ];
                                }
                            }
                        }
                    }
                @endphp

                @if(!empty($attachments))
                <div class="attachments-section">
                    <h4><i class='bx bx-paperclip'></i> Attachments ({{ count($attachments) }})</h4>
                    <div class="attachments-grid">
                        @foreach($attachments as $index => $attachment)
                        <div class="attachment-item">
                            <div class="attachment-icon">
                                @php
                                    $filename = $attachment['filename'] ?? 'Unknown File';
                                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                    $mimeType = $attachment['mime_type'] ?? '';

                                    // Determine icon based on file type
                                    $icon = 'bx bx-file';
                                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                                        $icon = 'bx bx-image';
                                    } elseif (in_array($extension, ['pdf'])) {
                                        $icon = 'bx bx-file-blank';
                                    } elseif (in_array($extension, ['doc', 'docx'])) {
                                        $icon = 'bx bx-file-doc';
                                    } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                        $icon = 'bx bx-file-blank';
                                    } elseif (in_array($extension, ['zip', 'rar', '7z'])) {
                                        $icon = 'bx bx-archive';
                                    } elseif (in_array($extension, ['txt', 'log'])) {
                                        $icon = 'bx bx-file-blank';
                                    }
                                @endphp
                                <i class='{{ $icon }}'></i>
                            </div>
                            <div class="attachment-details">
                                <div class="attachment-name">{{ $filename }}</div>
                                <div class="attachment-meta">
                                    {{ $mimeType ?: 'Unknown Type' }}
                                    @if(isset($attachment['size']))
                                        • {{ number_format($attachment['size'] / 1024, 1) }} KB
                                    @endif
                                </div>
                            </div>
                            <div class="attachment-actions">
                                @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'pdf', 'txt']))
                                <button class="btn btn-sm btn-outline-primary" onclick="previewAttachment({{ $email->id }}, {{ $index }}, '{{ $filename }}', '{{ $mimeType }}')">
                                    <i class='bx bx-show'></i> Preview
                                </button>
                                @endif
                                <button class="btn btn-sm btn-primary" onclick="downloadAttachment({{ $email->id }}, {{ $index }}, '{{ $filename }}')">
                                    <i class='bx bx-download'></i> Download
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="copyAttachmentLink({{ $email->id }}, {{ $index }})">
                                    <i class='bx bx-link'></i> Copy Link
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
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

                            // Apply character encoding fix to reply content
                            $replyContent = \App\Services\DesignersInboxEmailService::fixCharacterEncodingStatic($replyContent);

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        // Attachment preview function
        function previewAttachment(emailId, attachmentIndex, filename, mimeType) {
            // Create preview modal
            const modalHtml = `
                <div class="modal fade" id="attachmentPreviewModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class='bx bx-show'></i> Preview: ${filename}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div class="preview-loading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading preview...</p>
                                </div>
                                <div class="preview-content" style="display: none;">
                                    <iframe id="previewFrame" style="width: 100%; height: 500px; border: none;"></iframe>
                                </div>
                                <div class="preview-error" style="display: none;">
                                    <i class='bx bx-error-circle' style="font-size: 48px; color: #dc3545;"></i>
                                    <p class="mt-2">Preview not available for this file type</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="downloadAttachment(${emailId}, ${attachmentIndex}, '${filename}')">
                                    <i class='bx bx-download'></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            const existingModal = document.getElementById('attachmentPreviewModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('attachmentPreviewModal'));
            modal.show();

            // Load preview content
            const previewUrl = `/emails/${emailId}/attachment/${attachmentIndex}/preview`;
            const previewFrame = document.getElementById('previewFrame');
            const loadingDiv = document.querySelector('.preview-loading');
            const contentDiv = document.querySelector('.preview-content');
            const errorDiv = document.querySelector('.preview-error');

            previewFrame.onload = function() {
                loadingDiv.style.display = 'none';
                contentDiv.style.display = 'block';
            };

            previewFrame.onerror = function() {
                loadingDiv.style.display = 'none';
                errorDiv.style.display = 'block';
            };

            // Set preview source
            previewFrame.src = previewUrl;
        }

        // Attachment download function
        async function downloadAttachment(emailId, attachmentIndex, filename) {
            try {
                // Generate secure token for public download
                const token = await generateDownloadToken(emailId, attachmentIndex);
                const downloadUrl = `/emails/${emailId}/attachment/${attachmentIndex}/download/${token}`;

                // Create temporary link element
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = filename;
                link.style.display = 'none';

                // Add to page and click
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                showAlert('success', `Download started for ${filename}`);
            } catch (error) {
                console.error('Download error:', error);
                showAlert('error', 'Failed to generate download link');
            }
        }

        // Generate secure download token
        function generateDownloadToken(emailId, attachmentIndex) {
            // Generate token that matches server-side SHA256 hash
            const data = emailId + attachmentIndex + '{{ config("app.key") }}';
            return sha256(data);
        }

        // Simple SHA256 implementation for client-side
        async function sha256(message) {
            const msgBuffer = new TextEncoder().encode(message);
            const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        }

        // Copy attachment link to clipboard
        async function copyAttachmentLink(emailId, attachmentIndex) {
            try {
                const token = await generateDownloadToken(emailId, attachmentIndex);
                const downloadUrl = window.location.origin + `/emails/${emailId}/attachment/${attachmentIndex}/download/${token}`;

                // Copy to clipboard
                await navigator.clipboard.writeText(downloadUrl);
                showAlert('success', 'Download link copied to clipboard!');
            } catch (err) {
                console.error('Copy error:', err);
                showAlert('error', 'Failed to copy link');
            }
        }
    </script>
</body>
</html>
