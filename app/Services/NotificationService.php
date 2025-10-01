<?php

namespace App\Services;

use App\Models\UnifiedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a task notification
     */
    public function createTaskNotification($userId, $type, $title, $message, $data = [], $taskId = null, $priority = 'normal')
    {
        try {
            return UnifiedNotification::createTaskNotification($userId, $type, $title, $message, $data, $taskId, $priority);
        } catch (\Exception $e) {
            Log::error('Error creating task notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create an email notification
     */
    public function createEmailNotification($userId, $type, $title, $message, $data = [], $emailId = null, $priority = 'normal')
    {
        try {
            return UnifiedNotification::createEmailNotification($userId, $type, $title, $message, $data, $emailId, $priority);
        } catch (\Exception $e) {
            Log::error('Error creating email notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get notifications for a user
     */
    public function getUserNotifications($userId, $category = null, $limit = 50)
    {
        $query = UnifiedNotification::forUser($userId)
            ->active()
            ->orderBy('created_at', 'desc');

        if ($category) {
            $query->byCategory($category);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get unread notifications count for a user
     */
    public function getUnreadCount($userId, $category = null)
    {
        return UnifiedNotification::getUnreadCountForUser($userId, $category);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId = null)
    {
        $query = UnifiedNotification::where('id', $notificationId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $notification = $query->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId, $category = null)
    {
        return UnifiedNotification::markAllAsReadForUser($userId, $category);
    }

    /**
     * Delete notification
     */
    public function deleteNotification($notificationId, $userId = null)
    {
        $query = UnifiedNotification::where('id', $notificationId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $notification = $query->first();

        if ($notification) {
            $notification->delete();
            return true;
        }

        return false;
    }

    /**
     * Archive notification
     */
    public function archiveNotification($notificationId, $userId = null)
    {
        $query = UnifiedNotification::where('id', $notificationId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $notification = $query->first();

        if ($notification) {
            $notification->archive();
            return true;
        }

        return false;
    }

    /**
     * Get notification statistics for a user
     */
    public function getNotificationStats($userId)
    {
        $total = UnifiedNotification::forUser($userId)->active()->count();
        $unread = UnifiedNotification::getUnreadCountForUser($userId);
        $taskUnread = UnifiedNotification::getUnreadCountForUser($userId, 'task');
        $emailUnread = UnifiedNotification::getUnreadCountForUser($userId, 'email');

        return [
            'total' => $total,
            'unread' => $unread,
            'task_unread' => $taskUnread,
            'email_unread' => $emailUnread,
            'read' => $total - $unread,
        ];
    }

    /**
     * Create task-related notifications
     */
    public function notifyTaskAssigned($task, $assignedTo)
    {
        return $this->createTaskNotification(
            $assignedTo->id,
            'task_assigned',
            'New Task Assigned',
            "You have been assigned a new task: {$task->title}",
            ['task_id' => $task->id, 'project_id' => $task->project_id],
            $task->id,
            'normal'
        );
    }

    public function notifyTaskCompleted($task, $completedBy)
    {
        return $this->createTaskNotification(
            $completedBy->id,
            'task_completed',
            'Task Completed',
            "Task '{$task->title}' has been completed",
            ['task_id' => $task->id, 'project_id' => $task->project_id],
            $task->id,
            'normal'
        );
    }

    public function notifyTaskOverdue($task, $assignedTo)
    {
        return $this->createTaskNotification(
            $assignedTo->id,
            'task_overdue',
            'Task Overdue',
            "Task '{$task->title}' is overdue",
            ['task_id' => $task->id, 'project_id' => $task->project_id],
            $task->id,
            'high'
        );
    }

    /**
     * Create email-related notifications
     */
    public function notifyEmailReply($originalEmail, $replyEmail, $user)
    {
        return $this->createEmailNotification(
            $user->id,
            'email_reply',
            'Email Reply Received',
            "You received a reply to your email: {$originalEmail->subject}",
            [
                'original_email_id' => $originalEmail->id,
                'reply_email_id' => $replyEmail->id,
                'from' => $replyEmail->from_email,
                'subject' => $replyEmail->subject
            ],
            $replyEmail->id,
            'normal'
        );
    }

    public function notifyNewEmail($email, $user)
    {
        return $this->createEmailNotification(
            $user->id,
            'email_received',
            'New Email Received',
            "New email received: {$email->subject}",
            [
                'from' => $email->from_email,
                'subject' => $email->subject,
                'has_attachments' => !empty($email->attachments)
            ],
            $email->id,
            'normal'
        );
    }

    public function notifyEmailSent($email, $user)
    {
        return $this->createEmailNotification(
            $user->id,
            'email_sent',
            'Email Sent',
            "Your email has been sent: {$email->subject}",
            [
                'to' => $email->to_email,
                'subject' => $email->subject
            ],
            $email->id,
            'low'
        );
    }
}
