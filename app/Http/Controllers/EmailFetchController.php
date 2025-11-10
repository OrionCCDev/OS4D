<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\DesignersInboxEmailService;
use App\Models\Email;
use App\Models\User;

class EmailFetchController extends Controller
{
    protected $designersInboxService;

    public function __construct(DesignersInboxEmailService $designersInboxService)
    {
        $this->designersInboxService = $designersInboxService;
    }

    /**
     * Display all emails from engineering@orion-contracting.com inbox
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user is a manager
        if (!$user->isManager()) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Only managers can view the designers inbox.');
        }

        // Get search criteria from request
        $searchCriteria = [
            'maxResults' => $request->get('limit', 50),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'subject' => $request->get('subject'),
            'domain' => $request->get('domain'),
            'has_attachment' => $request->get('has_attachment'),
            'after' => $request->get('after'),
            'before' => $request->get('before'),
        ];

        // Remove empty criteria
        $searchCriteria = array_filter($searchCriteria, function ($value) {
            return !empty($value);
        });

        // Fetch emails from designers inbox
        $fetchResult = $this->designersInboxService->fetchAllEmails($searchCriteria['maxResults'] ?? 50);

        // Get stored emails from database (designers inbox only)
        $storedEmails = Email::where('email_source', 'designers_inbox')
            ->orderBy('received_at', 'desc')
            ->paginate(20);

        // Get email statistics
        $emailStats = $this->designersInboxService->getEmailStats();

        return view('emails.all-emails', [
            'fetchResult' => $fetchResult,
            'storedEmails' => $storedEmails,
            'emailStats' => $emailStats,
            'searchCriteria' => $searchCriteria,
            'user' => $user
        ]);
    }

    /**
     * Fetch emails from designers inbox and store in database
     */
    public function fetchAndStore(Request $request)
    {
        $user = Auth::user();

        // Check if user is a manager
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only managers can access designers inbox.'
            ], 403);
        }

        try {
            $maxResults = $request->get('maxResults', 100);

            // Fetch new emails from designers inbox (incremental)
            $fetchResult = $this->designersInboxService->fetchNewEmails($maxResults);

            if (!$fetchResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch emails from designers inbox',
                    'errors' => $fetchResult['errors']
                ], 500);
            }

            // Store emails in database
            $storeResult = $this->designersInboxService->storeEmailsInDatabase($fetchResult['emails'], $user);

            // Log the fetch operation for tracking
            $this->designersInboxService->logFetchOperation($fetchResult, $storeResult, $fetchResult['total_fetched']);

            return response()->json([
                'success' => true,
                'message' => 'New emails fetched and stored successfully',
                'data' => [
                    'fetched' => $fetchResult['total_fetched'],
                    'stored' => $storeResult['stored'],
                    'skipped' => $storeResult['skipped'],
                    'errors' => $storeResult['errors']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching and storing emails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search emails with criteria
     */
    public function search(Request $request)
    {
        $user = Auth::user();

        // Check if user is a manager
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only managers can access designers inbox.'
            ], 403);
        }

        try {
            $criteria = $request->only([
                'from',
                'to',
                'subject',
                'domain',
                'has_attachment',
                'after',
                'before',
                'maxResults'
            ]);

            $searchResult = $this->designersInboxService->searchEmails($criteria);

            return response()->json([
                'success' => $searchResult['success'],
                'emails' => $searchResult['emails'],
                'total_found' => $searchResult['total_found'],
                'errors' => $searchResult['errors']
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching emails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email details - redirect to standalone view
     */
    public function show($id)
    {
        // Redirect to standalone view for better email display
        return redirect()->route('emails.show-standalone', $id);
    }

    /**
     * Mark email as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();

        // Check if user is a manager
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only managers can access designers inbox.'
            ], 403);
        }

        $email = Email::where('id', $id)
            ->where('email_source', 'designers_inbox')
            ->firstOrFail();

        $email->update(['status' => 'read']);

        return response()->json(['success' => true]);
    }

    /**
     * Mark email as unread
     */
    public function markAsUnread($id)
    {
        $user = Auth::user();

        // Check if user is a manager
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only managers can access designers inbox.'
            ], 403);
        }

        $email = Email::where('id', $id)
            ->where('email_source', 'designers_inbox')
            ->firstOrFail();

        $email->update(['status' => 'received']);

        return response()->json(['success' => true]);
    }

    /**
     * Debug email parsing
     */
    public function debugEmail($id)
    {
        $user = Auth::user();

        // Check if user is a manager
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only managers can access designers inbox.'
            ], 403);
        }

        $email = Email::where('id', $id)
            ->where('email_source', 'designers_inbox')
            ->firstOrFail();

        $parsedBody = $this->parseEmailBody($email->body);

        // Sanitize data to prevent UTF-8 encoding issues
        $sanitizeString = function ($str) {
            if (empty($str)) return '';
            // Remove or replace invalid UTF-8 characters
            $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            // Remove any remaining invalid characters
            $str = filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            return $str;
        };

        return response()->json([
            'email_id' => $email->id,
            'original_body_length' => strlen($email->body),
            'parsed_body_length' => strlen($parsedBody),
            'original_body_preview' => $sanitizeString(substr($email->body, 0, 500)),
            'parsed_body_preview' => $sanitizeString(substr($parsedBody, 0, 500)),
            'has_html_content' => strpos($email->body, 'Content-Type: text/html') !== false,
            'has_boundary' => preg_match('/--([a-f0-9]+)/', $email->body) ? true : false,
            'parsed_body' => $sanitizeString($parsedBody),
            'subject' => $sanitizeString($email->subject),
            'from_email' => $sanitizeString($email->from_email)
        ]);
    }

    /**
     * Parse email body to extract HTML content
     */
    private function parseEmailBody($body)
    {
        if (empty($body)) {
            return '';
        }

        \Log::info('Parsing email body, length: ' . strlen($body));

        // Handle quoted-printable encoding first
        if (strpos($body, 'Content-Transfer-Encoding: quoted-printable') !== false) {
            $body = quoted_printable_decode($body);
            \Log::info('[parseEmailBody] Applied quoted-printable decoding, new length: ' . strlen($body));
        }

        // Ensure proper UTF-8 encoding
        $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');

        // Handle multipart emails with any boundary
        if (preg_match('/--([a-f0-9]+)/', $body, $matches)) {
            $boundary = '--' . $matches[1];
            \Log::info('[parseEmailBody] Found multipart email with boundary: ' . $boundary);

            // Split by the detected boundary
            $parts = explode($boundary, $body);
            \Log::info('[parseEmailBody] Number of parts after explode: ' . count($parts));

            foreach ($parts as $index => $part) {
                \Log::info('[parseEmailBody] Processing part ' . $index . ', length: ' . strlen($part));

                // Skip empty parts
                if (trim($part) === '' || trim($part) === '--') {
                    \Log::info('[parseEmailBody] Skipping empty part ' . $index);
                    continue;
                }

                // Look for HTML part
                if (strpos($part, 'Content-Type: text/html') !== false) {
                    \Log::info('[parseEmailBody] Found HTML part in part ' . $index);

                    // Find the start of HTML content (after headers)
                    $lines = explode("\n", $part);
                    $htmlContent = '';
                    $inContent = false;
                    $headerEnded = false;

                    foreach ($lines as $line) {
                        // Skip until we find the HTML content type
                        if (!$inContent && strpos($line, 'Content-Type: text/html') !== false) {
                            $inContent = true;
                            continue;
                        }

                        // After finding HTML content type, look for empty line to mark end of headers
                        if ($inContent && !$headerEnded) {
                            if (trim($line) === '') {
                                $headerEnded = true;
                                \Log::info('[parseEmailBody] Header ended in HTML part ' . $index);
                                continue;
                            }
                            // Skip header lines
                            if (strpos($line, 'Content-') === 0 || strpos($line, 'charset=') !== false) {
                                continue;
                            }
                        }

                        // Collect HTML content after headers
                        if ($inContent && $headerEnded) {
                            $htmlContent .= $line . "\n";
                        }
                    }

                    // Clean up the HTML content more carefully
                    $htmlContent = trim($htmlContent);

                    // If we still have MIME artifacts, try a different approach
                    if (strpos($htmlContent, 'Content-Type:') !== false || strpos($htmlContent, 'Content-Transfer-Encoding:') !== false) {
                        \Log::info('[parseEmailBody] Found MIME artifacts in HTML content, trying alternative extraction');

                        // Try to find HTML content after the last MIME header
                        $lines = explode("\n", $htmlContent);
                        $cleanContent = '';
                        $foundHtmlStart = false;

                        foreach ($lines as $line) {
                            if (!$foundHtmlStart && (strpos($line, '<html') !== false || strpos($line, '<div') !== false || strpos($line, '<p') !== false)) {
                                $foundHtmlStart = true;
                            }

                            if ($foundHtmlStart) {
                                $cleanContent .= $line . "\n";
                            }
                        }

                        if (!empty($cleanContent)) {
                            $htmlContent = $cleanContent;
                            \Log::info('[parseEmailBody] Alternative extraction successful, new length: ' . strlen($htmlContent));
                        }
                    }

                    // Remove any remaining MIME artifacts but preserve HTML content
                    $htmlContent = preg_replace('/^Content-Transfer-Encoding:.*$/m', '', $htmlContent);
                    $htmlContent = preg_replace('/^Content-Type:.*$/m', '', $htmlContent);
                    $htmlContent = preg_replace('/^charset=.*$/m', '', $htmlContent);

                    // Remove boundary markers but preserve HTML
                    $htmlContent = preg_replace('/^--[a-f0-9]+--?$/m', '', $htmlContent);

                    $htmlContent = trim($htmlContent);

                    \Log::info('[parseEmailBody] HTML content after cleaning, length: ' . strlen($htmlContent));
                    \Log::info('[parseEmailBody] HTML content preview: ' . substr($htmlContent, 0, 300));

                    // Check if we have actual HTML content
                    if (strlen($htmlContent) > 50 && (strpos($htmlContent, '<html') !== false || strpos($htmlContent, '<div') !== false || strpos($htmlContent, '<p') !== false)) {
                        \Log::info('[parseEmailBody] Returning HTML content from part ' . $index . ', length: ' . strlen($htmlContent));
                        return $htmlContent;
                    } else {
                        \Log::warning('[parseEmailBody] HTML content from part ' . $index . ' too short or no HTML tags found. Length: ' . strlen($htmlContent));
                        \Log::warning('[parseEmailBody] Raw HTML content: ' . substr($htmlContent, 0, 500));
                    }
                }

                // Look for plain text part if no HTML found
                if (strpos($part, 'Content-Type: text/plain') !== false) {
                    \Log::info('[parseEmailBody] Found plain text part in part ' . $index);

                    // Find the start of plain text content (after headers)
                    $lines = explode("\n", $part);
                    $textContent = '';
                    $inContent = false;
                    $headerEnded = false;

                    foreach ($lines as $line) {
                        // Skip until we find the plain text content type
                        if (!$inContent && strpos($line, 'Content-Type: text/plain') !== false) {
                            $inContent = true;
                            continue;
                        }

                        // After finding plain text content type, look for empty line to mark end of headers
                        if ($inContent && !$headerEnded) {
                            if (trim($line) === '') {
                                $headerEnded = true;
                                \Log::info('[parseEmailBody] Header ended in plain text part ' . $index);
                                continue;
                            }
                            // Skip header lines
                            if (strpos($line, 'Content-') === 0 || strpos($line, 'charset=') !== false) {
                                continue;
                            }
                        }

                        // Collect plain text content after headers
                        if ($inContent && $headerEnded) {
                            $textContent .= $line . "\n";
                        }
                    }

                    // Clean up the text content more carefully
                    $textContent = trim($textContent);

                    // Remove any remaining MIME artifacts but preserve text content
                    $textContent = preg_replace('/^Content-Transfer-Encoding:.*$/m', '', $textContent);
                    $textContent = preg_replace('/^Content-Type:.*$/m', '', $textContent);
                    $textContent = preg_replace('/^charset=.*$/m', '', $textContent);

                    // Remove boundary markers but preserve text
                    $textContent = preg_replace('/^--[a-f0-9]+--?$/m', '', $textContent);

                    $textContent = trim($textContent);

                    // Convert plain text to HTML
                    $textContent = htmlspecialchars($textContent);
                    $textContent = nl2br($textContent);

                    \Log::info('[parseEmailBody] Plain text content after cleaning, length: ' . strlen($textContent));
                    \Log::info('[parseEmailBody] Plain text content preview: ' . substr($textContent, 0, 300));

                    if (strlen($textContent) > 20) {
                        \Log::info('[parseEmailBody] Returning plain text content from part ' . $index . ', length: ' . strlen($textContent));
                        return $textContent;
                    } else {
                        \Log::warning('[parseEmailBody] Plain text content from part ' . $index . ' too short or empty after cleaning. Length: ' . strlen($textContent));
                    }
                }
            }

            // Try regex-based extraction as fallback
            \Log::info('[parseEmailBody] Trying regex-based HTML extraction as fallback');

            // Look for HTML content between boundaries using regex
            if (preg_match('/Content-Type: text\/html[^>]*>(.*?)(?=--[a-f0-9]+|$)/s', $body, $matches)) {
                $htmlContent = trim($matches[1]);
                \Log::info('[parseEmailBody] Regex extraction found HTML content, length: ' . strlen($htmlContent));

                if (strlen($htmlContent) > 50 && (strpos($htmlContent, '<html') !== false || strpos($htmlContent, '<div') !== false)) {
                    \Log::info('[parseEmailBody] Returning regex-extracted HTML content');
                    return $htmlContent;
                }
            }

            // Fallback if no HTML or plain text part was returned from multipart
            \Log::warning('[parseEmailBody] No suitable HTML or plain text part found in multipart email. Falling back to full body.');
            return $body;
        }

        // If it's already HTML, return as is
        if (strpos($body, '<html') !== false || strpos($body, '<div') !== false) {
            \Log::info('[parseEmailBody] Body appears to be HTML already. Returning as is.');
            return $body;
        }

        // If it's plain text, convert to HTML
        if (strpos($body, 'Content-Type: text/plain') !== false) {
            \Log::info('[parseEmailBody] Found plain text content type. Converting to HTML.');
            $lines = explode("\n", $body);
            $textStart = false;
            $textContent = '';

            foreach ($lines as $line) {
                if ($textStart) {
                    $textContent .= $line . "\n";
                } elseif (strpos($line, 'Content-Type: text/plain') !== false) {
                    $textStart = true;
                }
            }

            // Convert plain text to HTML
            $textContent = htmlspecialchars(trim($textContent));
            $textContent = nl2br($textContent);

            if (strlen($textContent) > 10) {
                \Log::info('[parseEmailBody] Returning converted plain text, length: ' . strlen($textContent));
                return $textContent;
            } else {
                \Log::warning('[parseEmailBody] Converted plain text too short or empty. Falling back to full body.');
            }
        }

        // Fallback: return the body as is
        \Log::warning('[parseEmailBody] Using final fallback - returning body as is, length: ' . strlen($body));
        return $body;
    }

    /**
     * Get email statistics
     */
    public function getStats()
    {
        $user = Auth::user();

        // Check if user is a manager
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only managers can access designers inbox.'
            ], 403);
        }

        try {
            $inboxStats = $this->designersInboxService->getEmailStats();

            $dbStats = [
                'total_stored' => Email::where('email_source', 'designers_inbox')->count(),
                'unread_count' => Email::where('email_source', 'designers_inbox')->where('status', 'received')->count(),
                'read_count' => Email::where('email_source', 'designers_inbox')->where('status', 'read')->count(),
                'replied_count' => Email::where('email_source', 'designers_inbox')->where('status', 'replied')->count(),
            ];

            return response()->json([
                'success' => true,
                'inbox_stats' => $inboxStats,
                'database_stats' => $dbStats
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting email stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while getting stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export emails to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        // Check if user is a manager
        if (!$user->isManager()) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Only managers can export designers inbox emails.');
        }

        $emails = Email::where('email_source', 'designers_inbox')
            ->orderBy('received_at', 'desc')
            ->get();

        $filename = 'emails_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($emails) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'From',
                'To',
                'Subject',
                'Received At',
                'Status',
                'Has Attachments',
                'Gmail Message ID',
                'Thread ID'
            ]);

            // CSV data
            foreach ($emails as $email) {
                fputcsv($file, [
                    $email->id,
                    $email->from_email,
                    $email->to_email,
                    $email->subject,
                    $email->received_at->format('Y-m-d H:i:s'),
                    $email->status,
                    !empty($email->attachments) ? 'Yes' : 'No',
                    $email->gmail_message_id,
                    $email->thread_id
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete email from database
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user->canDelete()) {
            abort(403, 'Access denied. Only admins and managers can delete emails.');
        }

        $email = Email::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $email->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Bulk operations on emails
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();
        $action = $request->get('action');
        $emailIds = $request->get('email_ids', []);

        if ($action === 'delete' && !$user->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete emails.'
            ], 403);
        }

        if (empty($emailIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No emails selected'
            ], 400);
        }

        try {
            $emails = Email::where('email_source', 'designers_inbox')
                ->whereIn('id', $emailIds)
                ->get();

            $count = 0;
            foreach ($emails as $email) {
                switch ($action) {
                    case 'mark_read':
                        $email->update(['status' => 'read']);
                        $count++;
                        break;
                    case 'mark_unread':
                        $email->update(['status' => 'received']);
                        $count++;
                        break;
                    case 'delete':
                        $email->delete();
                        $count++;
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$count} emails",
                'processed_count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk action: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during bulk operation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show email in standalone view (no layout constraints)
     */
    public function showStandalone($id)
    {
        $user = Auth::user();

        if (!$user->isManager()) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Only managers can view emails.');
        }

        try {
            $email = Email::findOrFail($id);

            // Mark as read if it's received
            if ($email->status === 'received') {
                $email->update(['status' => 'read']);
            }

            // Parse email body for better display
            $parsedBody = null;
            $parsedReplies = [];

            if ($email->body) {
                try {
                    // Try to parse HTML content
                    $dom = new \DOMDocument();
                    libxml_use_internal_errors(true);
                    $dom->loadHTML($email->body, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                    libxml_clear_errors();

                    $parsedBody = $dom->saveHTML();
                } catch (\Exception $e) {
                    // If parsing fails, use raw body
                    $parsedBody = $email->body;
                }
            }

            // Parse replies if any
            if ($email->replies && $email->replies->count() > 0) {
                foreach ($email->replies as $reply) {
                    if ($reply->body) {
                        try {
                            $dom = new \DOMDocument();
                            libxml_use_internal_errors(true);
                            $dom->loadHTML($reply->body, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                            libxml_clear_errors();
                            $parsedReplies[$reply->id] = $dom->saveHTML();
                        } catch (\Exception $e) {
                            $parsedReplies[$reply->id] = $reply->body;
                        }
                    }
                }
            }

            return view('emails.standalone-show', compact('email', 'parsedBody', 'parsedReplies'));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview email attachment
     */
    public function previewAttachment($emailId, $attachmentIndex)
    {
        $user = Auth::user();

        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only managers can view attachments.'
            ], 403);
        }

        try {
            $email = Email::findOrFail($emailId);
            $attachments = $email->attachments ?? [];

            if (!isset($attachments[$attachmentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment not found.'
                ], 404);
            }

            $attachment = $attachments[$attachmentIndex];
            $filename = $attachment['filename'] ?? 'unknown';
            $mimeType = $attachment['mime_type'] ?? 'application/octet-stream';
            $attachmentId = $attachment['attachment_id'] ?? null;

            // For Gmail attachments, we need to fetch from Gmail API
            if ($attachmentId && $email->email_source === 'gmail') {
                // This would require Gmail API integration
                // For now, return a placeholder
                return response('<div style="padding: 50px; text-align: center;"><h4>Gmail Attachment Preview</h4><p>Preview not available for Gmail attachments.</p><p>Please download the file to view it.</p></div>');
            }

            // For local attachments or if no attachment_id
            return response('<div style="padding: 50px; text-align: center;"><h4>Attachment Preview</h4><p>Preview not available for this file type.</p><p>Please download the file to view it.</p></div>');

        } catch (\Exception $e) {
            return response('<div style="padding: 50px; text-align: center;"><h4>Error</h4><p>Unable to load attachment preview.</p></div>');
        }
    }

    /**
     * Download email attachment
     */
    public function downloadAttachment($emailId, $attachmentIndex)
    {
        $user = Auth::user();

        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only managers can download attachments.'
            ], 403);
        }

        try {
            $email = Email::findOrFail($emailId);
            $attachments = $email->attachments ?? [];

            if (!isset($attachments[$attachmentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment not found.'
                ], 404);
            }

            $attachment = $attachments[$attachmentIndex];
            $filename = $attachment['filename'] ?? 'unknown';
            $mimeType = $attachment['mime_type'] ?? 'application/octet-stream';
            $attachmentId = $attachment['attachment_id'] ?? null;

            // For Gmail attachments, we need to fetch from Gmail API
            if ($attachmentId && $email->email_source === 'gmail') {
                // This would require Gmail API integration to fetch the actual file
                // For now, return a placeholder response
                return response()->json([
                    'success' => false,
                    'message' => 'Gmail attachment download not yet implemented. Please contact administrator.'
                ], 501);
            }

            // Check if attachment has content (base64 encoded)
            if (isset($attachment['content']) && !empty($attachment['content'])) {
                // Decode base64 content and serve it
                $fileContent = base64_decode($attachment['content']);
                if ($fileContent !== false) {
                    return response($fileContent, 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                        'Content-Length' => strlen($fileContent),
                    ]);
                }
            }

            // Check different storage locations for the file
            $storagePaths = [
                storage_path('app/email-attachments/' . $filename),
                storage_path('app/' . $filename),
            ];

            // If attachment has file_path, use that
            if (isset($attachment['file_path'])) {
                $storagePaths[] = storage_path('app/' . $attachment['file_path']);
            }

            // Try each path
            foreach ($storagePaths as $storagePath) {
                if (file_exists($storagePath)) {
                    return response()->download($storagePath, $filename, [
                        'Content-Type' => $mimeType,
                    ]);
                }
            }

            // If file doesn't exist locally, return error
            return response()->json([
                'success' => false,
                'message' => 'Attachment file not found on server. The attachment may not have been properly saved during email processing.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading attachment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download email attachment (public with token)
     */
    public function downloadAttachmentPublic($emailId, $attachmentIndex, $token)
    {
        try {
            // Verify token (simple hash verification)
            $expectedToken = hash('sha256', $emailId . $attachmentIndex . config('app.key'));
            if (!hash_equals($expectedToken, $token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid download token.'
                ], 403);
            }

            $email = Email::findOrFail($emailId);
            $attachments = $email->attachments ?? [];

            if (!isset($attachments[$attachmentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment not found.'
                ], 404);
            }

            $attachment = $attachments[$attachmentIndex];
            $filename = $attachment['filename'] ?? 'unknown';
            $mimeType = $attachment['mime_type'] ?? 'application/octet-stream';
            $attachmentId = $attachment['attachment_id'] ?? null;

            // For Gmail attachments, we need to fetch from Gmail API
            if ($attachmentId && $email->email_source === 'gmail') {
                return response()->json([
                    'success' => false,
                    'message' => 'Gmail attachment download not yet implemented. Please contact administrator.'
                ], 501);
            }

            // Check if attachment has content (base64 encoded)
            if (isset($attachment['content']) && !empty($attachment['content'])) {
                // Decode base64 content and serve it
                $fileContent = base64_decode($attachment['content']);
                if ($fileContent !== false) {
                    return response($fileContent, 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                        'Content-Length' => strlen($fileContent),
                    ]);
                }
            }

            // Check different storage locations for the file
            $storagePaths = [
                storage_path('app/email-attachments/' . $filename),
                storage_path('app/' . $filename),
            ];

            // If attachment has file_path, use that
            if (isset($attachment['file_path'])) {
                $storagePaths[] = storage_path('app/' . $attachment['file_path']);
            }

            // Try each path
            foreach ($storagePaths as $storagePath) {
                if (file_exists($storagePath)) {
                    return response()->download($storagePath, $filename, [
                        'Content-Type' => $mimeType,
                    ]);
                }
            }

            // If file doesn't exist locally, return error
            return response()->json([
                'success' => false,
                'message' => 'Attachment file not found on server.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading attachment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View email attachment in browser (inline)
     */
    public function viewAttachment($emailId, $attachmentIndex)
    {
        $user = Auth::user();

        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only managers can view attachments.'
            ], 403);
        }

        try {
            $email = Email::findOrFail($emailId);
            $attachments = $email->attachments ?? [];

            if (!isset($attachments[$attachmentIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment not found.'
                ], 404);
            }

            $attachment = $attachments[$attachmentIndex];
            $filename = $attachment['filename'] ?? 'unknown';
            $mimeType = $attachment['mime_type'] ?? 'application/octet-stream';
            $attachmentId = $attachment['attachment_id'] ?? null;

            // For Gmail attachments, we need to fetch from Gmail API
            if ($attachmentId && $email->email_source === 'gmail') {
                // This would require Gmail API integration to fetch the actual file
                return response()->json([
                    'success' => false,
                    'message' => 'Gmail attachment viewing not yet implemented. Please download the file instead.'
                ], 501);
            }

            // Check if attachment has content (base64 encoded)
            if (isset($attachment['content']) && !empty($attachment['content'])) {
                // Decode base64 content and serve it
                $fileContent = base64_decode($attachment['content']);
                if ($fileContent !== false) {
                    return response($fileContent, 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . $filename . '"',
                        'Content-Length' => strlen($fileContent),
                    ]);
                }
            }

            // Check different storage locations for the file
            $storagePaths = [
                storage_path('app/email-attachments/' . $filename),
                storage_path('app/' . $filename),
            ];

            // If attachment has file_path, use that
            if (isset($attachment['file_path'])) {
                $storagePaths[] = storage_path('app/' . $attachment['file_path']);
            }

            // Try each path
            foreach ($storagePaths as $storagePath) {
                if (file_exists($storagePath)) {
                    // Return file for inline viewing (not download)
                    return response()->file($storagePath, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    ]);
                }
            }

            // If file doesn't exist locally, return error
            return response()->json([
                'success' => false,
                'message' => 'Attachment file not found on server.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error viewing attachment: ' . $e->getMessage()
            ], 500);
        }
    }

}
