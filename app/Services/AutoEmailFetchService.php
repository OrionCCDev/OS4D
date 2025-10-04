<?php

namespace App\Services;

use App\Models\Email;
use App\Models\User;
use App\Models\EmailFetchLog;
use App\Models\DesignersInboxNotification;
use App\Services\DesignersInboxEmailService;
use App\Services\DesignersInboxNotificationService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AutoEmailFetchService
{
    protected $emailService;
    protected $notificationService;

    public function __construct(
        DesignersInboxEmailService $emailService,
        NotificationService $notificationService
    ) {
        $this->emailService = $emailService;
        $this->notificationService = $notificationService;
    }

    /**
     * Automatically fetch and process new emails
     */
    public function autoFetchAndProcess(): array
    {
        $result = [
            'success' => false,
            'fetched' => 0,
            'stored' => 0,
            'skipped' => 0,
            'notifications_created' => 0,
            'errors' => []
        ];

        try {
            // Use a different lock key to avoid conflicts with old code
            $lockKey = 'auto-email-fetch-v2:running';
            $lockValue = time() . '-' . uniqid();

            // Try to acquire lock atomically
            if (!Cache::add($lockKey, $lockValue, 300)) {
                Log::info('AutoEmailFetchService: Another auto-fetch is already running, skipping...');
                $result['success'] = true;
                $result['message'] = 'Another auto-fetch is already running';
                return $result;
            }

            try {
                // Get manager user for email association
                $manager = User::whereIn('role', ['admin', 'manager'])->first();
                if (!$manager) {
                    $result['errors'][] = 'No manager user found';
                    return $result;
                }

                Log::info('AutoEmailFetchService: Starting automatic email fetch for manager: ' . $manager->name);

                // Fetch new emails (incremental)
                $fetchResult = $this->emailService->fetchNewEmails(50); // Limit to 50 for auto-fetch

                Log::info('AutoEmailFetchService: Fetch result', [
                    'success' => $fetchResult['success'],
                    'total_fetched' => $fetchResult['total_fetched'],
                    'errors' => $fetchResult['errors'] ?? []
                ]);

                if (!$fetchResult['success']) {
                    $result['errors'] = array_merge($result['errors'], $fetchResult['errors']);
                    return $result;
                }

                $result['fetched'] = $fetchResult['total_fetched'];

                if ($result['fetched'] === 0) {
                    Log::info('AutoEmailFetchService: No new emails found');
                    $result['success'] = true;
                    $result['message'] = 'No new emails found';
                    return $result;
                }

                // Store emails in database
                $storeResult = $this->emailService->storeEmailsInDatabase($fetchResult['emails'], $manager);
                $result['stored'] = $storeResult['stored'];
                $result['skipped'] = $storeResult['skipped'];
                $result['errors'] = array_merge($result['errors'], $storeResult['errors']);

                // Create notifications only for newly stored emails
                $notificationCount = $this->createNotificationsForStoredEmails($storeResult['stored_emails'] ?? []);
                $result['notifications_created'] = $notificationCount;

                // Log the operation
                $this->emailService->logFetchOperation($fetchResult, $storeResult, $fetchResult['current_message_count'] ?? $fetchResult['total_fetched']);

                $result['success'] = true;
                $result['message'] = "Successfully processed {$result['fetched']} emails, stored {$result['stored']} new emails, created {$result['notifications_created']} notifications";

                Log::info('AutoEmailFetchService: ' . $result['message']);

            } finally {
                // Always release the lock
                Cache::forget($lockKey);
            }

        } catch (\Exception $e) {
            Log::error('AutoEmailFetchService: Exception occurred: ' . $e->getMessage());
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Create notifications for stored emails
     */
    protected function createNotificationsForStoredEmails(array $storedEmails): int
    {
        $notificationCount = 0;

        try {
            foreach ($storedEmails as $email) {
                // Create notification for managers
                $this->notificationService->createNewEmailNotification($email);
                $notificationCount++;

                // If it's a reply, create reply notification
                if ($this->isReplyEmail($email->subject)) {
                    $this->notificationService->createReplyNotification($email);
                    $notificationCount++;
                }

                Log::info("AutoEmailFetchService: Created notification for email: {$email->subject}");
            }

        } catch (\Exception $e) {
            Log::error('AutoEmailFetchService: Error creating notifications: ' . $e->getMessage());
        }

        return $notificationCount;
    }

    /**
     * Check if email is a reply
     */
    protected function isReplyEmail(string $subject): bool
    {
        return preg_match('/^(Re:|RE:|Fwd:|FWD:)/i', $subject);
    }

    /**
     * Get unread notifications count for managers
     */
    public function getUnreadNotificationsCount(): int
    {
        try {
            $managers = User::whereIn('role', ['admin', 'manager'])->get();
            $totalCount = 0;

            foreach ($managers as $manager) {
                $count = DesignersInboxNotification::where('user_id', $manager->id)
                    ->unread()
                    ->count();
                $totalCount += $count;
            }

            return $totalCount;

        } catch (\Exception $e) {
            Log::error('AutoEmailFetchService: Error getting unread count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get recent notifications for managers
     */
    public function getRecentNotifications(int $limit = 10): array
    {
        try {
            $managers = User::whereIn('role', ['admin', 'manager'])->pluck('id');

            return DesignersInboxNotification::whereIn('user_id', $managers)
                ->with('email')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();

        } catch (\Exception $e) {
            Log::error('AutoEmailFetchService: Error getting recent notifications: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notifications as read for a manager
     */
    public function markNotificationsAsRead(int $managerId): bool
    {
        try {
            $updated = DesignersInboxNotification::where('user_id', $managerId)
                ->unread()
                ->update(['read_at' => now()]);

            Log::info("AutoEmailFetchService: Marked {$updated} notifications as read for manager {$managerId}");
            return true;

        } catch (\Exception $e) {
            Log::error('AutoEmailFetchService: Error marking notifications as read: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email fetch statistics
     */
    public function getFetchStatistics(): array
    {
        try {
            $lastFetch = EmailFetchLog::getLatestForSource('designers_inbox');
            $totalEmails = Email::where('email_source', 'designers_inbox')->count();
            $unreadEmails = Email::where('email_source', 'designers_inbox')
                ->where('status', 'received')
                ->count();
            $unreadNotifications = $this->getUnreadNotificationsCount();

            return [
                'last_fetch_at' => $lastFetch ? $lastFetch->last_fetch_at : null,
                'total_emails' => $totalEmails,
                'unread_emails' => $unreadEmails,
                'unread_notifications' => $unreadNotifications,
                'last_fetch_status' => $lastFetch ? $lastFetch->last_errors : null,
            ];

        } catch (\Exception $e) {
            Log::error('AutoEmailFetchService: Error getting statistics: ' . $e->getMessage());
            return [];
        }
    }
}
