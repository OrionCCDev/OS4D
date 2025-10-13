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
     * Notify managers when an email is received in engineering@orion-contracting.com inbox
     */
    public function notifyManagersAboutReceivedEmail(array $emailData): void
    {
        try {
            $managers = User::where('role', 'admin')->orWhere('role', 'manager')->get();

            if ($managers->isEmpty()) {
                Log::warning('No managers found to notify about received email');
                return;
            }

            // Extract email details
            $fromEmail = $emailData['from_email'] ?? 'Unknown';
            $subject = $emailData['subject'] ?? 'No Subject';
            $receivedAt = $emailData['date'] ?? now();
            $messageId = $emailData['message_id'] ?? 'Unknown';

            // Try to find related task from subject or content
            $relatedTask = $this->findRelatedTask($subject, $emailData['body'] ?? '');

            foreach ($managers as $manager) {
                // Create in-app notification
                $this->createInAppNotification($manager, $emailData, $relatedTask);

                // Send email notification
                $this->sendEmailNotification($manager, $emailData, $relatedTask);
            }

            Log::info('Engineering inbox notifications sent to ' . $managers->count() . ' managers for email: ' . $messageId);

        } catch (\Exception $e) {
            Log::error('Failed to notify managers about received email: ' . $e->getMessage());
        }
    }

    /**
     * Create in-app notification for manager
     */
    private function createInAppNotification(User $manager, array $emailData, ?Task $relatedTask): void
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
                'is_read' => false
            ]);

            Log::info('In-app notification created for manager: ' . $manager->email);

        } catch (\Exception $e) {
            Log::error('Failed to create in-app notification: ' . $e->getMessage());
        }
    }

    /**
     * Send email notification to manager
     */
    private function sendEmailNotification(User $manager, array $emailData, ?Task $relatedTask): void
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
