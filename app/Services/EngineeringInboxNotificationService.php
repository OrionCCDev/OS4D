<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\UnifiedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\EngineeringInboxReceivedMail;

class EngineeringInboxNotificationService
{
    /**
     * Notify managers and users when an email is received in engineering@orion-contracting.com inbox
     */
    public function notifyManagersAboutReceivedEmail(array $emailData): void
    {
        try {
            // Extract email details
            $fromEmail = $emailData['from_email'] ?? 'Unknown';
            $subject = $emailData['subject'] ?? 'No Subject';
            $receivedAt = $emailData['date'] ?? now();
            $messageId = $emailData['message_id'] ?? 'Unknown';

            // Try to find related task from subject or content
            $relatedTask = $this->findRelatedTask($subject, $emailData['body'] ?? '');

            // Notify managers (ALL emails to engineering@orion-contracting.com)
            $this->notifyManagers($emailData, $relatedTask);

            // Notify users (only if their email is involved)
            $this->notifyRelevantUsers($emailData, $relatedTask);

            Log::info('Engineering inbox notifications processed for email: ' . $messageId);

        } catch (\Exception $e) {
            Log::error('Failed to notify about received email: ' . $e->getMessage());
        }
    }

    /**
     * Notify all managers about received email
     */
    private function notifyManagers(array $emailData, ?Task $relatedTask): void
    {
        try {
            $managers = User::whereIn('role', ['admin', 'manager', 'sub-admin', 'sup-admin'])->get();

            if ($managers->isEmpty()) {
                Log::warning('No managers found to notify about received email');
                return;
            }

            foreach ($managers as $manager) {
                // Create in-app notification
                $this->createManagerInAppNotification($manager, $emailData, $relatedTask);

                // Send email notification - DISABLED (only in-app notifications needed)
                // $this->sendManagerEmailNotification($manager, $emailData, $relatedTask);
            }

            Log::info('Engineering inbox notifications sent to ' . $managers->count() . ' managers');

        } catch (\Exception $e) {
            Log::error('Failed to notify managers about received email: ' . $e->getMessage());
        }
    }

    /**
     * Notify users whose email is involved in the received email
     */
    private function notifyRelevantUsers(array $emailData, ?Task $relatedTask): void
    {
        try {
            // Get all users
            $users = User::where('role', 'user')->get();
            $involvedUsers = [];

            foreach ($users as $user) {
                if ($this->isUserInvolvedInEmail($emailData, $user)) {
                    $involvedUsers[] = $user;
                }
            }

            if (empty($involvedUsers)) {
                Log::info('No users involved in the received email');
                return;
            }

            foreach ($involvedUsers as $user) {
                // Create in-app notification
                $this->createUserInAppNotification($user, $emailData, $relatedTask);
            }

            Log::info('Engineering inbox notifications sent to ' . count($involvedUsers) . ' involved users');

        } catch (\Exception $e) {
            Log::error('Failed to notify users about received email: ' . $e->getMessage());
        }
    }

    /**
     * Check if a user's email is involved in the received email
     */
    private function isUserInvolvedInEmail(array $emailData, User $user): bool
    {
        $userEmail = strtolower(trim($user->email));

        // Check if user's email is in FROM field
        if (isset($emailData['from_email']) && strpos(strtolower($emailData['from_email']), $userEmail) !== false) {
            return true;
        }

        // Check if user's email is in TO field
        if (isset($emailData['to_email']) && strpos(strtolower($emailData['to_email']), $userEmail) !== false) {
            return true;
        }

        // Check if user's email is in CC field
        if (isset($emailData['cc']) && strpos(strtolower($emailData['cc']), $userEmail) !== false) {
            return true;
        }

        // Check if user's email is in BCC field
        if (isset($emailData['bcc']) && strpos(strtolower($emailData['bcc']), $userEmail) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Create in-app notification for manager
     */
    private function createManagerInAppNotification(User $manager, array $emailData, ?Task $relatedTask): void
    {
        try {
            $fromEmail = $emailData['from_email'] ?? 'Unknown';
            $subject = $emailData['subject'] ?? 'No Subject';

            $message = "New email received in engineering inbox from {$fromEmail}";
            if ($relatedTask) {
                $message .= " (Related to Task #{$relatedTask->id}: {$relatedTask->title})";
            }

            UnifiedNotification::create([
                'user_id' => $manager->id,
                'category' => 'email',
                'type' => 'engineering_inbox_received',
                'title' => 'New Email Received',
                'message' => $message,
                'data' => [
                    'from_email' => $fromEmail,
                    'subject' => $subject,
                    'received_at' => $emailData['date'] ?? now(),
                    'message_id' => $emailData['message_id'] ?? null,
                    'task_id' => $relatedTask?->id,
                    'task_title' => $relatedTask?->title,
                ],
                'action_url' => $relatedTask ? route('tasks.show', $relatedTask->id) : null,
                'is_read' => false
            ]);

            Log::info('In-app notification created for manager: ' . $manager->email);

        } catch (\Exception $e) {
            Log::error('Failed to create manager in-app notification: ' . $e->getMessage());
        }
    }

    /**
     * Create in-app notification for user
     */
    private function createUserInAppNotification(User $user, array $emailData, ?Task $relatedTask): void
    {
        try {
            $fromEmail = $emailData['from_email'] ?? 'Unknown';
            $subject = $emailData['subject'] ?? 'No Subject';
            $userEmail = strtolower(trim($user->email));

            // Determine involvement type
            $involvementType = $this->getUserInvolvementType($emailData, $userEmail);

            $message = "Email involving you received in engineering inbox from {$fromEmail}";
            if ($relatedTask) {
                $message .= " (Related to Task #{$relatedTask->id}: {$relatedTask->title})";
            }

            UnifiedNotification::create([
                'user_id' => $user->id,
                'category' => 'email',
                'type' => 'engineering_inbox_user_involved',
                'title' => 'Email Involving You Received',
                'message' => $message,
                'data' => [
                    'from_email' => $fromEmail,
                    'subject' => $subject,
                    'received_at' => $emailData['date'] ?? now(),
                    'message_id' => $emailData['message_id'] ?? null,
                    'task_id' => $relatedTask?->id,
                    'task_title' => $relatedTask?->title,
                    'involvement_type' => $involvementType,
                    'user_email' => $user->email,
                ],
                'action_url' => $relatedTask ? route('tasks.show', $relatedTask->id) : null,
                'is_read' => false
            ]);

            Log::info('In-app notification created for user: ' . $user->email . ' (involvement: ' . $involvementType . ')');

        } catch (\Exception $e) {
            Log::error('Failed to create user in-app notification: ' . $e->getMessage());
        }
    }

    /**
     * Get the type of user involvement in the email
     */
    private function getUserInvolvementType(array $emailData, string $userEmail): string
    {
        // Check if user's email is in FROM field
        if (isset($emailData['from_email']) && strpos(strtolower($emailData['from_email']), $userEmail) !== false) {
            return 'sent_by_you';
        }

        // Check if user's email is in TO field
        if (isset($emailData['to_email']) && strpos(strtolower($emailData['to_email']), $userEmail) !== false) {
            return 'addressed_to_you';
        }

        // Check if user's email is in CC field
        if (isset($emailData['cc']) && strpos(strtolower($emailData['cc']), $userEmail) !== false) {
            return 'cc_to_you';
        }

        // Check if user's email is in BCC field
        if (isset($emailData['bcc']) && strpos(strtolower($emailData['bcc']), $userEmail) !== false) {
            return 'bcc_to_you';
        }

        return 'involved';
    }

    /**
     * Send email notification to manager
     */
    private function sendManagerEmailNotification(User $manager, array $emailData, ?Task $relatedTask): void
    {
        try {
            $mail = new EngineeringInboxReceivedMail($emailData, $manager, $relatedTask);
            $mail->from('engineering@orion-contracting.com', 'Orion Engineering System');

            Mail::send($mail);

            Log::info('Email notification sent to manager: ' . $manager->email);

        } catch (\Exception $e) {
            Log::error('Failed to send email notification to manager: ' . $e->getMessage());
        }
    }

    /**
     * Try to find related task from email subject or content
     */
    private function findRelatedTask(string $subject, string $body): ?Task
    {
        try {
            // Look for task ID in subject (e.g., "Task #123", "Task ID: 123")
            if (preg_match('/task\s*#?(\d+)/i', $subject, $matches)) {
                $taskId = (int) $matches[1];
                $task = Task::find($taskId);
                if ($task) {
                    Log::info('Found related task from subject: ' . $taskId);
                    return $task;
                }
            }

            // Look for task ID in body
            if (preg_match('/task\s*#?(\d+)/i', $body, $matches)) {
                $taskId = (int) $matches[1];
                $task = Task::find($taskId);
                if ($task) {
                    Log::info('Found related task from body: ' . $taskId);
                    return $task;
                }
            }

            // Look for task title in subject or body
            $tasks = Task::where('status', 'on_client_consultant_review')
                        ->orWhere('status', 'in_review_after_client_consultant_reply')
                        ->get();

            foreach ($tasks as $task) {
                if (stripos($subject, $task->title) !== false || stripos($body, $task->title) !== false) {
                    Log::info('Found related task by title: ' . $task->id);
                    return $task;
                }
            }

            Log::info('No related task found for email subject: ' . $subject);
            return null;

        } catch (\Exception $e) {
            Log::error('Error finding related task: ' . $e->getMessage());
            return null;
        }
    }
}
