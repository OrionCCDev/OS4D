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
     * Display all emails from designers@orion-contracting.com inbox
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
        $searchCriteria = array_filter($searchCriteria, function($value) {
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

            // Fetch emails from designers inbox
            $fetchResult = $this->designersInboxService->fetchAllEmails($maxResults);

            if (!$fetchResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch emails from designers inbox',
                    'errors' => $fetchResult['errors']
                ], 500);
            }

            // Store emails in database
            $storeResult = $this->designersInboxService->storeEmailsInDatabase($fetchResult['emails'], $user);

            return response()->json([
                'success' => true,
                'message' => 'Emails fetched and stored successfully',
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
                'from', 'to', 'subject', 'domain', 'has_attachment',
                'after', 'before', 'maxResults'
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
     * Get email details
     */
    public function show($id)
    {
        $user = Auth::user();

        // Check if user is a manager
        if (!$user->isManager()) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Only managers can view designers inbox emails.');
        }

        $email = Email::where('id', $id)
            ->where('email_source', 'designers_inbox')
            ->firstOrFail();

        // Mark as read if not already
        if ($email->status === 'received') {
            $email->update(['status' => 'read']);
        }

        // Parse the email body to extract HTML content
        $parsedBody = $this->parseEmailBody($email->body);

        return view('emails.designers-inbox-show', compact('email', 'parsedBody'));
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

        return response()->json([
            'email_id' => $email->id,
            'original_body_length' => strlen($email->body),
            'parsed_body_length' => strlen($parsedBody),
            'original_body_preview' => substr($email->body, 0, 500),
            'parsed_body_preview' => substr($parsedBody, 0, 500),
            'has_html_content' => strpos($email->body, 'Content-Type: text/html') !== false,
            'has_boundary' => strpos($email->body, '--0000000000009d3e4c064011fe0b') !== false,
            'parsed_body' => $parsedBody
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
        }

        // Handle multipart emails with any boundary
        if (preg_match('/--([a-f0-9]+)/', $body, $matches)) {
            $boundary = '--' . $matches[1];
            \Log::info('Found multipart email with boundary: ' . $boundary);

            // Split by the detected boundary
            $parts = explode($boundary, $body);

            foreach ($parts as $index => $part) {
                \Log::info("Processing part $index, length: " . strlen($part));

                // Skip empty parts
                if (trim($part) === '' || trim($part) === '--') {
                    continue;
                }

                // Look for HTML part
                if (strpos($part, 'Content-Type: text/html') !== false) {
                    \Log::info('Found HTML part');

                    // Extract HTML content after headers
                    $lines = explode("\n", $part);
                    $htmlStart = false;
                    $htmlContent = '';

                    foreach ($lines as $line) {
                        if ($htmlStart) {
                            $htmlContent .= $line . "\n";
                        } elseif (strpos($line, 'Content-Type: text/html') !== false) {
                            $htmlStart = true;
                        }
                    }

                    // Clean up the HTML content
                    $htmlContent = trim($htmlContent);
                    $htmlContent = preg_replace('/^Content-Transfer-Encoding:.*$/m', '', $htmlContent);
                    $htmlContent = preg_replace('/^Content-Type:.*$/m', '', $htmlContent);
                    $htmlContent = preg_replace('/^charset=.*$/m', '', $htmlContent);
                    $htmlContent = preg_replace('/^\s*$/m', '', $htmlContent);

                    $htmlContent = trim($htmlContent);

                    if (!empty($htmlContent) && strlen($htmlContent) > 50) {
                        \Log::info('Extracted HTML content length: ' . strlen($htmlContent));
                        return $htmlContent;
                    }
                }

                // Look for plain text part if no HTML found
                if (strpos($part, 'Content-Type: text/plain') !== false) {
                    \Log::info('Found plain text part');

                    $lines = explode("\n", $part);
                    $textStart = false;
                    $textContent = '';

                    foreach ($lines as $line) {
                        if ($textStart) {
                            $textContent .= $line . "\n";
                        } elseif (strpos($line, 'Content-Type: text/plain') !== false) {
                            $textStart = true;
                        }
                    }

                    // Clean up the text content
                    $textContent = trim($textContent);
                    $textContent = preg_replace('/^Content-Transfer-Encoding:.*$/m', '', $textContent);
                    $textContent = preg_replace('/^Content-Type:.*$/m', '', $textContent);
                    $textContent = preg_replace('/^charset=.*$/m', '', $textContent);

                    // Convert plain text to HTML
                    $textContent = htmlspecialchars($textContent);
                    $textContent = nl2br($textContent);

                    if (!empty($textContent) && strlen($textContent) > 20) {
                        \Log::info('Converted plain text to HTML, length: ' . strlen($textContent));
                        return $textContent;
                    }
                }
            }
        }

        // If it's already HTML, return as is
        if (strpos($body, '<html') !== false || strpos($body, '<div') !== false) {
            \Log::info('Body appears to be HTML already');
            return $body;
        }

        // If it's plain text, convert to HTML
        if (strpos($body, 'Content-Type: text/plain') !== false) {
            \Log::info('Found plain text content type');

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

            return $textContent;
        }

        // Fallback: return the body as is
        \Log::info('Using fallback - returning body as is');
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

        $callback = function() use ($emails) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID', 'From', 'To', 'Subject', 'Received At', 'Status',
                'Has Attachments', 'Gmail Message ID', 'Thread ID'
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
}
