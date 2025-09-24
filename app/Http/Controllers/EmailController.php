<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Task;
use App\Models\Email;
use App\Notifications\EmailReceivedNotification;

class EmailController extends Controller
{
    /**
     * Handle incoming email webhook
     */
    public function handleIncomingEmail(Request $request)
    {
        try {
            // Log the incoming email data
            Log::info('Incoming email received', $request->all());

            // Extract email data (format depends on your email service)
            $emailData = $this->parseEmailData($request);

            // Store email in database
            $email = Email::create([
                'from_email' => $emailData['from'],
                'to_email' => $emailData['to'],
                'subject' => $emailData['subject'],
                'body' => $emailData['body'],
                'received_at' => now(),
                'status' => 'received'
            ]);

            // Find related task if subject contains task reference
            $task = $this->findRelatedTask($emailData['subject']);

            if ($task) {
                $email->update(['task_id' => $task->id]);

                // Notify task assignee
                if ($task->assignee) {
                    $task->assignee->notify(new EmailReceivedNotification($email, $task));
                }
            }

            // Send general notification to relevant users
            $this->sendEmailNotifications($email);

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Error handling incoming email: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Parse email data from webhook request
     */
    private function parseEmailData(Request $request)
    {
        // This depends on your email service provider
        // Example for Mailgun webhook format
        return [
            'from' => $request->input('sender'),
            'to' => $request->input('recipient'),
            'subject' => $request->input('subject'),
            'body' => $request->input('body-plain'),
            'attachments' => $request->input('attachments', [])
        ];
    }

    /**
     * Find related task based on email subject
     */
    private function findRelatedTask($subject)
    {
        // Look for task ID patterns in subject
        if (preg_match('/Task #(\d+)/', $subject, $matches)) {
            return Task::find($matches[1]);
        }

        // Look for task title patterns
        $tasks = Task::where('status', '!=', 'completed')->get();
        foreach ($tasks as $task) {
            if (str_contains(strtolower($subject), strtolower($task->title))) {
                return $task;
            }
        }

        return null;
    }

    /**
     * Send notifications for received email
     */
    private function sendEmailNotifications($email)
    {
        // Notify managers
        $managers = User::where('role', 'manager')->get();
        foreach ($managers as $manager) {
            $manager->notify(new EmailReceivedNotification($email));
        }

        // Notify users who might be interested
        $interestedUsers = User::whereHas('tasks', function($query) use ($email) {
            $query->where('status', '!=', 'completed');
        })->get();

        foreach ($interestedUsers as $user) {
            $user->notify(new EmailReceivedNotification($email));
        }
    }

    /**
     * Check for new emails via IMAP
     */
    public function checkNewEmails()
    {
        try {
            // IMAP configuration
            $hostname = config('mail.imap.host');
            $username = config('mail.imap.username');
            $password = config('mail.imap.password');

            $connection = imap_open($hostname, $username, $password);

            if (!$connection) {
                throw new \Exception('IMAP connection failed');
            }

            $emails = imap_search($connection, 'UNSEEN');

            if ($emails) {
                foreach ($emails as $emailNumber) {
                    $header = imap_headerinfo($connection, $emailNumber);
                    $body = imap_body($connection, $emailNumber);

                    // Process each email
                    $this->processImapEmail($header, $body);

                    // Mark as read
                    imap_setflag_full($connection, $emailNumber, '\\Seen');
                }
            }

            imap_close($connection);

            return response()->json(['status' => 'success', 'count' => count($emails ?? [])]);

        } catch (\Exception $e) {
            Log::error('IMAP check failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Process email from IMAP
     */
    private function processImapEmail($header, $body)
    {
        $emailData = [
            'from' => $header->from[0]->mailbox . '@' . $header->from[0]->host,
            'to' => $header->to[0]->mailbox . '@' . $header->to[0]->host,
            'subject' => $header->subject,
            'body' => $body,
            'received_at' => date('Y-m-d H:i:s', $header->udate)
        ];

        // Store and process email
        $email = Email::create([
            'from_email' => $emailData['from'],
            'to_email' => $emailData['to'],
            'subject' => $emailData['subject'],
            'body' => $emailData['body'],
            'received_at' => $emailData['received_at'],
            'status' => 'received'
        ]);

        // Send notifications
        $this->sendEmailNotifications($email);
    }

    /**
     * Display a listing of emails
     */
    public function index()
    {
        $emails = Email::with('task')
            ->orderBy('received_at', 'desc')
            ->paginate(20);

        return view('emails.index', compact('emails'));
    }

    /**
     * Display the specified email
     */
    public function show(Email $email)
    {
        // Mark as read if not already
        if ($email->status === 'received') {
            $email->update(['status' => 'read']);
        }

        return view('emails.show', compact('email'));
    }

    /**
     * Mark email as read
     */
    public function markAsRead(Email $email)
    {
        $email->update(['status' => 'read']);

        return response()->json(['status' => 'success']);
    }
}
