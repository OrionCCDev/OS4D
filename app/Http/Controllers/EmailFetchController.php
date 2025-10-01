<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\GmailEmailFetchService;
use App\Models\Email;
use App\Models\User;

class EmailFetchController extends Controller
{
    protected $gmailEmailFetchService;

    public function __construct(GmailEmailFetchService $gmailEmailFetchService)
    {
        $this->gmailEmailFetchService = $gmailEmailFetchService;
    }

    /**
     * Display all emails from user's Gmail account
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has Gmail connected
        if (!$user->gmail_connected) {
            return redirect()->route('gmail.redirect')
                ->with('error', 'Please connect your Gmail account first to view emails.');
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

        // Fetch emails from Gmail
        $fetchResult = $this->gmailEmailFetchService->fetchAllEmails($user, $searchCriteria['maxResults'] ?? 50);

        // Get stored emails from database
        $storedEmails = Email::where('user_id', $user->id)
            ->orderBy('received_at', 'desc')
            ->paginate(20);

        // Get email statistics
        $emailStats = $this->gmailEmailFetchService->getEmailStats($user);

        return view('emails.all-emails', [
            'fetchResult' => $fetchResult,
            'storedEmails' => $storedEmails,
            'emailStats' => $emailStats,
            'searchCriteria' => $searchCriteria,
            'user' => $user
        ]);
    }

    /**
     * Fetch emails from Gmail and store in database
     */
    public function fetchAndStore(Request $request)
    {
        $user = Auth::user();

        if (!$user->gmail_connected) {
            return response()->json([
                'success' => false,
                'message' => 'Gmail not connected'
            ], 400);
        }

        try {
            $maxResults = $request->get('maxResults', 100);

            // Fetch emails from Gmail
            $fetchResult = $this->gmailEmailFetchService->fetchAllEmails($user, $maxResults);

            if (!$fetchResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch emails',
                    'errors' => $fetchResult['errors']
                ], 500);
            }

            // Store emails in database
            $storeResult = $this->gmailEmailFetchService->storeEmailsInDatabase($fetchResult['emails'], $user);

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

        if (!$user->gmail_connected) {
            return response()->json([
                'success' => false,
                'message' => 'Gmail not connected'
            ], 400);
        }

        try {
            $criteria = $request->only([
                'from', 'to', 'subject', 'domain', 'has_attachment',
                'after', 'before', 'maxResults'
            ]);

            $searchResult = $this->gmailEmailFetchService->searchEmails($user, $criteria);

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
        $email = Email::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Mark as read if not already
        if ($email->status === 'received') {
            $email->update(['status' => 'read']);
        }

        return view('emails.show', compact('email'));
    }

    /**
     * Get email statistics
     */
    public function getStats()
    {
        $user = Auth::user();

        if (!$user->gmail_connected) {
            return response()->json([
                'success' => false,
                'message' => 'Gmail not connected'
            ], 400);
        }

        try {
            $gmailStats = $this->gmailEmailFetchService->getEmailStats($user);

            $dbStats = [
                'total_stored' => Email::where('user_id', $user->id)->count(),
                'unread_count' => Email::where('user_id', $user->id)->where('status', 'received')->count(),
                'read_count' => Email::where('user_id', $user->id)->where('status', 'read')->count(),
                'replied_count' => Email::where('user_id', $user->id)->where('status', 'replied')->count(),
            ];

            return response()->json([
                'success' => true,
                'gmail_stats' => $gmailStats,
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

        $emails = Email::where('user_id', $user->id)
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
     * Mark email as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $email = Email::where('id', $id)
            ->where('user_id', $user->id)
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
        $email = Email::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $email->update(['status' => 'received']);

        return response()->json(['success' => true]);
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
            $emails = Email::where('user_id', $user->id)
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
