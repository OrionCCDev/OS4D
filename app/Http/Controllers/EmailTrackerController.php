<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailTrackerController extends Controller
{
    /**
     * Show email tracker dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search', '');
        $filter = $request->get('filter', 'all'); // all, sent, received, replies

        // Base query for emails
        $query = Email::query();

        if ($user->isManager()) {
            // Manager can see ALL emails
            $query->where(function($q) {
                $q->where('from_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('to_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('cc', 'like', '%designers@orion-contracting.com%');
            });
        } else {
            // Regular users can only see emails they're involved in
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id) // Emails they sent
                  ->orWhere('to_email', $user->email) // Emails sent to them
                  ->orWhere('cc', 'like', '%' . $user->email . '%') // Emails they were CC'd on
                  ->orWhereHas('replies', function($replyQuery) use ($user) {
                      $replyQuery->where('user_id', $user->id); // Replies to their emails
                  });
            });
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', '%' . $search . '%')
                  ->orWhere('from_email', 'like', '%' . $search . '%')
                  ->orWhere('to_email', 'like', '%' . $search . '%')
                  ->orWhere('body', 'like', '%' . $search . '%');
            });
        }

        // Apply type filter
        switch ($filter) {
            case 'sent':
                $query->where('email_type', 'sent');
                break;
            case 'received':
                $query->where('email_type', 'received');
                break;
            case 'replies':
                $query->whereNotNull('reply_to_email_id');
                break;
            case 'designers':
                $query->where(function($q) {
                    $q->where('from_email', 'like', '%designers@orion-contracting.com%')
                      ->orWhere('to_email', 'like', '%designers@orion-contracting.com%')
                      ->orWhere('cc', 'like', '%designers@orion-contracting.com%');
                });
                break;
        }

        // Get emails with relationships
        $emails = $query->with(['user', 'replies', 'notifications'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Get statistics
        $stats = $this->getEmailStats($user);

        // Get user's unread notifications
        $unreadCount = EmailNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('emails.tracker', compact(
            'emails',
            'stats',
            'unreadCount',
            'search',
            'filter'
        ));
    }

    /**
     * Get email statistics
     */
    protected function getEmailStats(User $user)
    {
        $baseQuery = Email::query();

        if ($user->isManager()) {
            // Manager sees all emails
            $baseQuery->where(function($q) {
                $q->where('from_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('to_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('cc', 'like', '%designers@orion-contracting.com%');
            });
        } else {
            // User sees only their emails
            $baseQuery->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('to_email', $user->email)
                  ->orWhere('cc', 'like', '%' . $user->email . '%')
                  ->orWhereHas('replies', function($replyQuery) use ($user) {
                      $replyQuery->where('user_id', $user->id);
                  });
            });
        }

        return [
            'total_emails' => $baseQuery->count(),
            'sent_emails' => (clone $baseQuery)->where('email_type', 'sent')->count(),
            'received_emails' => (clone $baseQuery)->where('email_type', 'received')->count(),
            'replies' => (clone $baseQuery)->whereNotNull('reply_to_email_id')->count(),
            'designers_emails' => (clone $baseQuery)->where(function($q) {
                $q->where('from_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('to_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('cc', 'like', '%designers@orion-contracting.com%');
            })->count(),
            'emails_today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'emails_this_week' => (clone $baseQuery)->where('created_at', '>=', now()->subWeek())->count(),
        ];
    }

    /**
     * Get email details
     */
    public function show($id)
    {
        $user = Auth::user();

        $email = Email::with(['user', 'replies', 'notifications', 'task'])
            ->find($id);

        if (!$email) {
            abort(404, 'Email not found');
        }

        // Check if user can view this email
        if (!$this->canUserViewEmail($user, $email)) {
            abort(403, 'You do not have permission to view this email');
        }

        return view('emails.show', compact('email'));
    }

    /**
     * Check if user can view email
     */
    protected function canUserViewEmail(User $user, Email $email): bool
    {
        if ($user->isManager()) {
            return true; // Manager can see all emails
        }

        // Check if user is involved in this email
        return $email->user_id === $user->id || // User sent it
               $email->to_email === $user->email || // User is recipient
               str_contains($email->cc ?? '', $user->email) || // User is CC'd
               $email->replies()->where('user_id', $user->id)->exists(); // User has replies
    }

    /**
     * Get email statistics via API
     */
    public function getStats()
    {
        $user = Auth::user();
        $stats = $this->getEmailStats($user);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Search emails via API
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q', '');
        $filter = $request->get('filter', 'all');
        $perPage = $request->get('per_page', 20);

        $query = Email::query();

        if ($user->isManager()) {
            $query->where(function($q) {
                $q->where('from_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('to_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('cc', 'like', '%designers@orion-contracting.com%');
            });
        } else {
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('to_email', $user->email)
                  ->orWhere('cc', 'like', '%' . $user->email . '%')
                  ->orWhereHas('replies', function($replyQuery) use ($user) {
                      $replyQuery->where('user_id', $user->id);
                  });
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', '%' . $search . '%')
                  ->orWhere('from_email', 'like', '%' . $search . '%')
                  ->orWhere('to_email', 'like', '%' . $search . '%')
                  ->orWhere('body', 'like', '%' . $search . '%');
            });
        }

        switch ($filter) {
            case 'sent':
                $query->where('email_type', 'sent');
                break;
            case 'received':
                $query->where('email_type', 'received');
                break;
            case 'replies':
                $query->whereNotNull('reply_to_email_id');
                break;
            case 'designers':
                $query->where(function($q) {
                    $q->where('from_email', 'like', '%designers@orion-contracting.com%')
                      ->orWhere('to_email', 'like', '%designers@orion-contracting.com%')
                      ->orWhere('cc', 'like', '%designers@orion-contracting.com%');
                });
                break;
        }

        $emails = $query->with(['user', 'replies'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'emails' => $emails
        ]);
    }

    /**
     * Mark email as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();

        $email = Email::find($id);
        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Email not found'], 404);
        }

        if (!$this->canUserViewEmail($user, $email)) {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        // Mark related notifications as read
        EmailNotification::where('user_id', $user->id)
            ->where('email_id', $email->id)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Export emails to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'all');

        $query = Email::query();

        if ($user->isManager()) {
            $query->where(function($q) {
                $q->where('from_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('to_email', 'like', '%designers@orion-contracting.com%')
                  ->orWhere('cc', 'like', '%designers@orion-contracting.com%');
            });
        } else {
            $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('to_email', $user->email)
                  ->orWhere('cc', 'like', '%' . $user->email . '%')
                  ->orWhereHas('replies', function($replyQuery) use ($user) {
                      $replyQuery->where('user_id', $user->id);
                  });
            });
        }

        switch ($filter) {
            case 'sent':
                $query->where('email_type', 'sent');
                break;
            case 'received':
                $query->where('email_type', 'received');
                break;
            case 'replies':
                $query->whereNotNull('reply_to_email_id');
                break;
            case 'designers':
                $query->where(function($q) {
                    $q->where('from_email', 'like', '%designers@orion-contracting.com%')
                      ->orWhere('to_email', 'like', '%designers@orion-contracting.com%')
                      ->orWhere('cc', 'like', '%designers@orion-contracting.com%');
                });
                break;
        }

        $emails = $query->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'emails_' . $filter . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($emails) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID', 'Type', 'From', 'To', 'CC', 'Subject', 'Body', 'Sent By', 'Created At', 'Status'
            ]);

            // CSV data
            foreach ($emails as $email) {
                fputcsv($file, [
                    $email->id,
                    $email->email_type,
                    $email->from_email,
                    $email->to_email,
                    $email->cc ?? '',
                    $email->subject,
                    strip_tags($email->body),
                    $email->user ? $email->user->name : 'Unknown',
                    $email->created_at->format('Y-m-d H:i:s'),
                    $email->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
