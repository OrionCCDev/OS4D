<?php

namespace App\Services;

use App\Models\UnifiedNotification;
use App\Models\User;
use App\Models\Email;
use App\Events\NewNotification;
use App\Events\NotificationCountUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Create a task notification
     */
    public function createTaskNotification($userId, $type, $title, $message, $data = [], $taskId = null, $priority = 'normal')
    {
        try {
            $notification = UnifiedNotification::createTaskNotification($userId, $type, $title, $message, $data, $taskId, $priority);

            // Broadcast the new notification in real-time
            if ($notification) {
                broadcast(new NewNotification($notification))->toOthers();

                // Broadcast updated counts
                $counts = $this->getNotificationCounts($userId);
                broadcast(new NotificationCountUpdated($userId, $counts))->toOthers();

                Log::info('Task notification created and broadcasted', [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'type' => $type
                ]);
            }

            return $notification;
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
            $notification = UnifiedNotification::createEmailNotification($userId, $type, $title, $message, $data, $emailId, $priority);

            // Broadcast the new notification in real-time
            if ($notification) {
                broadcast(new NewNotification($notification))->toOthers();

                // Broadcast updated counts
                $counts = $this->getNotificationCounts($userId);
                broadcast(new NotificationCountUpdated($userId, $counts))->toOthers();

                Log::info('Email notification created and broadcasted', [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'type' => $type
                ]);
            }

            return $notification;
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
     * Get notification counts for broadcasting (simplified version for real-time updates)
     */
    public function getNotificationCounts($userId)
    {
        $taskUnread = UnifiedNotification::getUnreadCountForUser($userId, 'task');
        $emailUnread = UnifiedNotification::getUnreadCountForUser($userId, 'email');
        $total = $taskUnread + $emailUnread;

        return [
            'total' => $total,
            'task' => $taskUnread,
            'email' => $emailUnread,
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

    /**
     * Create notification for new email (used by AutoEmailFetchService)
     */
    public function createNewEmailNotification(Email $email)
    {
        try {
            // Get all managers and admins
            $managers = User::whereIn('role', ['admin', 'manager'])->get();
            $currentUserId = Auth::id();

            foreach ($managers as $manager) {
                // Don't send notification to the current user (manager who performed the action)
                if ($manager->id === $currentUserId) {
                    continue;
                }

                // Check if notification already exists to prevent duplicates
                $existingNotification = UnifiedNotification::where('user_id', $manager->id)
                    ->where('email_id', $email->id)
                    ->where('type', 'email_received')
                    ->first();

                if ($existingNotification) {
                    Log::info("UnifiedNotification already exists for email ID: {$email->id}, user ID: {$manager->id} (notification ID: {$existingNotification->id})");
                    continue; // Skip creating duplicate notification
                }

                $this->createEmailNotification(
                    $manager->id,
                    'email_received',
                    'New Email Received',
                    "New email received from {$email->from_email}: {$email->subject}",
                    [
                        'from' => $email->from_email,
                        'subject' => $email->subject,
                        'has_attachments' => !empty($email->attachments),
                        'email_source' => $email->email_source ?? 'designers_inbox'
                    ],
                    $email->id,
                    'normal'
                );

                Log::info("Created UnifiedNotification for new email: {$email->subject} for user: {$manager->id}");
            }

            // NEW: Also notify users (role: 'user') if their email is in CC or TO
            $this->createUserEmailNotifications($email);

        } catch (\Exception $e) {
            Log::error('Error creating new email notification: ' . $e->getMessage());
        }
    }

    /**
     * Create notifications for users (role: 'user') when their email is relevant
     */
    public function createUserEmailNotifications(Email $email)
    {
        try {
            // Get all users (role: 'user')
            $users = User::where('role', 'user')->get();
            
            foreach ($users as $user) {
                // Check if user's email is in CC, TO, or if this is a reply to their email
                if ($this->isEmailRelevantToUser($email, $user)) {
                    // Check if notification already exists to prevent duplicates
                    $existingNotification = UnifiedNotification::where('user_id', $user->id)
                        ->where('email_id', $email->id)
                        ->where('type', 'email_received')
                        ->first();

                    if ($existingNotification) {
                        Log::info("User notification already exists for email ID: {$email->id}, user ID: {$user->id} (notification ID: {$existingNotification->id})");
                        continue;
                    }

                    // Determine notification message based on relevance
                    $message = $this->getUserEmailNotificationMessage($email, $user);

                    $this->createEmailNotification(
                        $user->id,
                        'email_received',
                        'New Email Received',
                        $message,
                        [
                            'from' => $email->from_email,
                            'subject' => $email->subject,
                            'has_attachments' => !empty($email->attachments),
                            'email_source' => $email->email_source ?? 'designers_inbox',
                            'relevance_reason' => $this->getEmailRelevanceReason($email, $user)
                        ],
                        $email->id,
                        'normal'
                    );

                    Log::info("Created user notification for email: {$email->subject} for user: {$user->id} ({$user->email})");
                }
            }

        } catch (\Exception $e) {
            Log::error('Error creating user email notifications: ' . $e->getMessage());
        }
    }

    /**
     * Check if an email is relevant to a user
     */
    private function isEmailRelevantToUser(Email $email, User $user): bool
    {
        $userEmail = strtolower(trim($user->email));
        
        // Check if user's email is in TO field
        if ($email->to_email && strpos(strtolower($email->to_email), $userEmail) !== false) {
            return true;
        }
        
        // Check if user's email is in CC field
        if ($email->cc && strpos(strtolower($email->cc), $userEmail) !== false) {
            return true;
        }
        
        // Check if this is a reply to an email where the user was involved
        if ($email->in_reply_to_email_id) {
            $originalEmail = Email::find($email->in_reply_to_email_id);
            if ($originalEmail) {
                // Check if user was in the original email's TO or CC
                if ($originalEmail->to_email && strpos(strtolower($originalEmail->to_email), $userEmail) !== false) {
                    return true;
                }
                if ($originalEmail->cc && strpos(strtolower($originalEmail->cc), $userEmail) !== false) {
                    return true;
                }
            }
        }
        
        // Check if this is a reply to an email sent by the user
        if ($email->in_reply_to_email_id) {
            $originalEmail = Email::find($email->in_reply_to_email_id);
            if ($originalEmail && strpos(strtolower($originalEmail->from_email), $userEmail) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get the notification message for user email notifications
     */
    private function getUserEmailNotificationMessage(Email $email, User $user): string
    {
        $userEmail = strtolower(trim($user->email));
        
        // Check if user's email is in TO field
        if ($email->to_email && strpos(strtolower($email->to_email), $userEmail) !== false) {
            return "New email sent to you from {$email->from_email}: {$email->subject}";
        }
        
        // Check if user's email is in CC field
        if ($email->cc && strpos(strtolower($email->cc), $userEmail) !== false) {
            return "New email (you're CC'd) from {$email->from_email}: {$email->subject}";
        }
        
        // Check if this is a reply to an email where the user was involved
        if ($email->in_reply_to_email_id) {
            $originalEmail = Email::find($email->in_reply_to_email_id);
            if ($originalEmail) {
                if ($originalEmail->to_email && strpos(strtolower($originalEmail->to_email), $userEmail) !== false) {
                    return "Reply received to your email from {$email->from_email}: {$email->subject}";
                }
                if ($originalEmail->cc && strpos(strtolower($originalEmail->cc), $userEmail) !== false) {
                    return "Reply received to email you were CC'd on from {$email->from_email}: {$email->subject}";
                }
                if (strpos(strtolower($originalEmail->from_email), $userEmail) !== false) {
                    return "Reply received to your email from {$email->from_email}: {$email->subject}";
                }
            }
        }
        
        return "New email received from {$email->from_email}: {$email->subject}";
    }

    /**
     * Get the reason why the email is relevant to the user
     */
    private function getEmailRelevanceReason(Email $email, User $user): string
    {
        $userEmail = strtolower(trim($user->email));
        
        if ($email->to_email && strpos(strtolower($email->to_email), $userEmail) !== false) {
            return 'addressed_to_user';
        }
        
        if ($email->cc && strpos(strtolower($email->cc), $userEmail) !== false) {
            return 'user_ccd';
        }
        
        if ($email->in_reply_to_email_id) {
            $originalEmail = Email::find($email->in_reply_to_email_id);
            if ($originalEmail) {
                if ($originalEmail->to_email && strpos(strtolower($originalEmail->to_email), $userEmail) !== false) {
                    return 'reply_to_user_email';
                }
                if ($originalEmail->cc && strpos(strtolower($originalEmail->cc), $userEmail) !== false) {
                    return 'reply_to_user_ccd_email';
                }
                if (strpos(strtolower($originalEmail->from_email), $userEmail) !== false) {
                    return 'reply_to_user_sent_email';
                }
            }
        }
        
        return 'general_relevance';
    }

    /**
     * Create notification for email reply (used by AutoEmailFetchService)
     */
    public function createReplyNotification(Email $email)
    {
        try {
            // Get all managers and admins
            $managers = User::whereIn('role', ['admin', 'manager'])->get();
            $currentUserId = Auth::id();

            foreach ($managers as $manager) {
                // Don't send notification to the current user (manager who performed the action)
                if ($manager->id === $currentUserId) {
                    continue;
                }

                // Check if notification already exists to prevent duplicates
                $existingNotification = UnifiedNotification::where('user_id', $manager->id)
                    ->where('email_id', $email->id)
                    ->where('type', 'email_reply')
                    ->first();

                if ($existingNotification) {
                    Log::info("UnifiedNotification reply already exists for email ID: {$email->id}, user ID: {$manager->id} (notification ID: {$existingNotification->id})");
                    continue; // Skip creating duplicate notification
                }

                $this->createEmailNotification(
                    $manager->id,
                    'email_reply',
                    'Email Reply Received',
                    "You received a reply from {$email->from_email} regarding: {$email->subject}",
                    [
                        'from' => $email->from_email,
                        'subject' => $email->subject,
                        'has_attachments' => !empty($email->attachments),
                        'email_source' => $email->email_source ?? 'designers_inbox'
                    ],
                    $email->id,
                    'normal'
                );

                Log::info("Created UnifiedNotification for email reply: {$email->subject} for user: {$manager->id}");
            }

            // NEW: Also notify users (role: 'user') if this reply is relevant to them
            $this->createUserReplyNotifications($email);

        } catch (\Exception $e) {
            Log::error('Error creating reply notification: ' . $e->getMessage());
        }
    }

    /**
     * Create reply notifications for users (role: 'user') when the reply is relevant
     */
    public function createUserReplyNotifications(Email $email)
    {
        try {
            // Get all users (role: 'user')
            $users = User::where('role', 'user')->get();
            
            foreach ($users as $user) {
                // Check if this reply is relevant to the user
                if ($this->isReplyRelevantToUser($email, $user)) {
                    // Check if notification already exists to prevent duplicates
                    $existingNotification = UnifiedNotification::where('user_id', $user->id)
                        ->where('email_id', $email->id)
                        ->where('type', 'email_reply')
                        ->first();

                    if ($existingNotification) {
                        Log::info("User reply notification already exists for email ID: {$email->id}, user ID: {$user->id} (notification ID: {$existingNotification->id})");
                        continue;
                    }

                    // Determine notification message based on relevance
                    $message = $this->getUserReplyNotificationMessage($email, $user);

                    $this->createEmailNotification(
                        $user->id,
                        'email_reply',
                        'Email Reply Received',
                        $message,
                        [
                            'from' => $email->from_email,
                            'subject' => $email->subject,
                            'has_attachments' => !empty($email->attachments),
                            'email_source' => $email->email_source ?? 'designers_inbox',
                            'relevance_reason' => $this->getReplyRelevanceReason($email, $user)
                        ],
                        $email->id,
                        'normal'
                    );

                    Log::info("Created user reply notification for email: {$email->subject} for user: {$user->id} ({$user->email})");
                }
            }

        } catch (\Exception $e) {
            Log::error('Error creating user reply notifications: ' . $e->getMessage());
        }
    }

    /**
     * Check if a reply email is relevant to a user
     */
    private function isReplyRelevantToUser(Email $email, User $user): bool
    {
        $userEmail = strtolower(trim($user->email));
        
        // Check if user's email is in TO field
        if ($email->to_email && strpos(strtolower($email->to_email), $userEmail) !== false) {
            return true;
        }
        
        // Check if user's email is in CC field
        if ($email->cc && strpos(strtolower($email->cc), $userEmail) !== false) {
            return true;
        }
        
        // Check if this is a reply to an email where the user was involved
        if ($email->in_reply_to_email_id) {
            $originalEmail = Email::find($email->in_reply_to_email_id);
            if ($originalEmail) {
                // Check if user was in the original email's TO or CC
                if ($originalEmail->to_email && strpos(strtolower($originalEmail->to_email), $userEmail) !== false) {
                    return true;
                }
                if ($originalEmail->cc && strpos(strtolower($originalEmail->cc), $userEmail) !== false) {
                    return true;
                }
                // Check if user sent the original email
                if (strpos(strtolower($originalEmail->from_email), $userEmail) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Get the notification message for user reply notifications
     */
    private function getUserReplyNotificationMessage(Email $email, User $user): string
    {
        $userEmail = strtolower(trim($user->email));
        
        // Check if user's email is in TO field
        if ($email->to_email && strpos(strtolower($email->to_email), $userEmail) !== false) {
            return "Reply sent to you from {$email->from_email}: {$email->subject}";
        }
        
        // Check if user's email is in CC field
        if ($email->cc && strpos(strtolower($email->cc), $userEmail) !== false) {
            return "Reply (you're CC'd) from {$email->from_email}: {$email->subject}";
        }
        
        // Check if this is a reply to an email where the user was involved
        if ($email->in_reply_to_email_id) {
            $originalEmail = Email::find($email->in_reply_to_email_id);
            if ($originalEmail) {
                if ($originalEmail->to_email && strpos(strtolower($originalEmail->to_email), $userEmail) !== false) {
                    return "Reply to your email from {$email->from_email}: {$email->subject}";
                }
                if ($originalEmail->cc && strpos(strtolower($originalEmail->cc), $userEmail) !== false) {
                    return "Reply to email you were CC'd on from {$email->from_email}: {$email->subject}";
                }
                if (strpos(strtolower($originalEmail->from_email), $userEmail) !== false) {
                    return "Reply to your email from {$email->from_email}: {$email->subject}";
                }
            }
        }
        
        return "Reply received from {$email->from_email}: {$email->subject}";
    }

    /**
     * Get the reason why the reply is relevant to the user
     */
    private function getReplyRelevanceReason(Email $email, User $user): string
    {
        $userEmail = strtolower(trim($user->email));
        
        if ($email->to_email && strpos(strtolower($email->to_email), $userEmail) !== false) {
            return 'reply_addressed_to_user';
        }
        
        if ($email->cc && strpos(strtolower($email->cc), $userEmail) !== false) {
            return 'reply_user_ccd';
        }
        
        if ($email->in_reply_to_email_id) {
            $originalEmail = Email::find($email->in_reply_to_email_id);
            if ($originalEmail) {
                if ($originalEmail->to_email && strpos(strtolower($originalEmail->to_email), $userEmail) !== false) {
                    return 'reply_to_user_email';
                }
                if ($originalEmail->cc && strpos(strtolower($originalEmail->cc), $userEmail) !== false) {
                    return 'reply_to_user_ccd_email';
                }
                if (strpos(strtolower($originalEmail->from_email), $userEmail) !== false) {
                    return 'reply_to_user_sent_email';
                }
            }
        }
        
        return 'general_reply_relevance';
    }
}
