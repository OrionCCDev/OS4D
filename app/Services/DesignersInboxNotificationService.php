<?php

namespace App\Services;

use App\Models\DesignersInboxNotification;
use App\Models\Email;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DesignersInboxNotificationService
{
    /**
     * Create notification for new email in designers inbox
     */
    public function createNewEmailNotification(Email $email): void
    {
        try {
            // Get all managers
            $managers = User::where('role', 'manager')->get();

            foreach ($managers as $manager) {
                DesignersInboxNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $email->id,
                    'type' => 'new_email',
                    'title' => 'New Email in Designers Inbox',
                    'message' => "New email from {$email->from_email}: {$email->subject}",
                    'data' => [
                        'from_email' => $email->from_email,
                        'subject' => $email->subject,
                        'received_at' => $email->received_at->toISOString(),
                        'has_attachments' => !empty($email->attachments),
                        'attachment_count' => count($email->attachments ?? []),
                    ]
                ]);
            }

            Log::info("Created designers inbox notifications for email ID: {$email->id}");

        } catch (\Exception $e) {
            Log::error('Error creating designers inbox notification: ' . $e->getMessage());
        }
    }


    /**
     * Check if email is a reply
     */
    protected function isReplyEmail(string $subject): bool
    {
        return preg_match('/^(Re:|RE:|Fwd:|FWD:)/i', $subject);
    }

    /**
     * Create notification for email reply
     */
    public function createReplyNotification(Email $email): void
    {
        try {
            // Get all managers
            $managers = User::where('role', 'manager')->get();

            foreach ($managers as $manager) {
                DesignersInboxNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $email->id,
                    'type' => 'email_reply',
                    'title' => 'Email Reply in Designers Inbox',
                    'message' => "Reply from {$email->from_email}: {$email->subject}",
                    'data' => [
                        'from_email' => $email->from_email,
                        'subject' => $email->subject,
                        'received_at' => $email->received_at->toISOString(),
                        'is_reply' => true,
                        'original_subject' => preg_replace('/^(Re:|RE:)\s*/i', '', $email->subject),
                    ]
                ]);
            }

            Log::info("Created designers inbox reply notifications for email ID: {$email->id}");

        } catch (\Exception $e) {
            Log::error('Error creating designers inbox reply notification: ' . $e->getMessage());
        }
    }

    /**
     * Create notification for email with attachments
     */
    public function createAttachmentNotification(Email $email): void
    {
        try {
            // Get all managers
            $managers = User::where('role', 'manager')->get();

            foreach ($managers as $manager) {
                DesignersInboxNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $email->id,
                    'type' => 'email_attachment',
                    'title' => 'Email with Attachments',
                    'message' => "Email from {$email->from_email} has " . count($email->attachments) . " attachment(s)",
                    'data' => [
                        'from_email' => $email->from_email,
                        'subject' => $email->subject,
                        'received_at' => $email->received_at->toISOString(),
                        'attachments' => $email->attachments,
                        'attachment_count' => count($email->attachments),
                    ]
                ]);
            }

            Log::info("Created designers inbox attachment notifications for email ID: {$email->id}");

        } catch (\Exception $e) {
            Log::error('Error creating designers inbox attachment notification: ' . $e->getMessage());
        }
    }

    /**
     * Create urgent email notification
     */
    public function createUrgentNotification(Email $email, string $reason = 'Urgent email received'): void
    {
        try {
            // Get all managers
            $managers = User::where('role', 'manager')->get();

            foreach ($managers as $manager) {
                DesignersInboxNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $email->id,
                    'type' => 'email_urgent',
                    'title' => 'Urgent Email Alert',
                    'message' => "URGENT: {$reason} - {$email->subject}",
                    'data' => [
                        'from_email' => $email->from_email,
                        'subject' => $email->subject,
                        'received_at' => $email->received_at->toISOString(),
                        'urgency_reason' => $reason,
                        'priority' => 'high',
                    ]
                ]);
            }

            Log::info("Created urgent designers inbox notification for email ID: {$email->id}");

        } catch (\Exception $e) {
            Log::error('Error creating urgent designers inbox notification: ' . $e->getMessage());
        }
    }

    /**
     * Get unread notifications count for user
     */
    public function getUnreadCount(User $user): int
    {
        return DesignersInboxNotification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Get recent notifications for user
     */
    public function getRecentNotifications(User $user, int $limit = 10): array
    {
        return DesignersInboxNotification::where('user_id', $user->id)
            ->with('email')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, User $user): bool
    {
        try {
            $notification = DesignersInboxNotification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();

            if ($notification) {
                $notification->markAsRead();
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error marking designers inbox notification as read: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(User $user): bool
    {
        try {
            DesignersInboxNotification::where('user_id', $user->id)
                ->unread()
                ->update(['read_at' => now()]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error marking all designers inbox notifications as read: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean old notifications (older than 30 days)
     */
    public function cleanOldNotifications(): int
    {
        try {
            $deletedCount = DesignersInboxNotification::where('created_at', '<', now()->subDays(30))
                ->delete();

            Log::info("Cleaned {$deletedCount} old designers inbox notifications");
            return $deletedCount;

        } catch (\Exception $e) {
            Log::error('Error cleaning old designers inbox notifications: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if email should trigger urgent notification
     */
    public function shouldTriggerUrgentNotification(Email $email): bool
    {
        $urgentKeywords = [
            'urgent', 'asap', 'immediately', 'emergency', 'critical',
            'deadline', 'rush', 'priority', 'important'
        ];

        $subject = strtolower($email->subject);
        $body = strtolower($email->body);

        foreach ($urgentKeywords as $keyword) {
            if (strpos($subject, $keyword) !== false || strpos($body, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process email and create appropriate notifications
     */
    public function processEmailNotifications(Email $email): void
    {
        try {
            // Always create new email notification
            $this->createNewEmailNotification($email);

            // Check for attachments
            if (!empty($email->attachments) && count($email->attachments) > 0) {
                $this->createAttachmentNotification($email);
            }

            // Check for reply
            if ($email->is_reply) {
                $this->createReplyNotification($email);
            }

            // Check for urgent content
            if ($this->shouldTriggerUrgentNotification($email)) {
                $this->createUrgentNotification($email, 'Contains urgent keywords');
            }

        } catch (\Exception $e) {
            Log::error('Error processing email notifications: ' . $e->getMessage());
        }
    }
}
