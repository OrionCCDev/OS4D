<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UnifiedNotification;
use App\Models\CustomNotification;
use App\Models\EmailNotification;
use App\Models\DesignersInboxNotification;

class MigrateNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:migrate {--dry-run : Show what would be migrated without actually migrating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing notifications to the unified notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No data will be migrated');
        }

        $this->info('Starting notification migration...');

        // Migrate CustomNotifications
        $this->migrateCustomNotifications($dryRun);

        // Migrate EmailNotifications
        $this->migrateEmailNotifications($dryRun);

        // Migrate DesignersInboxNotifications
        $this->migrateDesignersInboxNotifications($dryRun);

        $this->info('✅ Notification migration completed!');
    }

    protected function migrateCustomNotifications($dryRun = false)
    {
        $this->info('📧 Migrating CustomNotifications...');

        $customNotifications = CustomNotification::all();
        $this->info("Found {$customNotifications->count()} custom notifications");

        $migrated = 0;
        foreach ($customNotifications as $notification) {
            if ($dryRun) {
                $this->line("  Would migrate: {$notification->title} (Type: {$notification->type})");
                $migrated++;
                continue;
            }

            try {
                // Determine category based on type
                $category = $this->determineCategory($notification->type);

                UnifiedNotification::create([
                    'user_id' => $notification->user_id,
                    'category' => $category,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'is_read' => $notification->read,
                    'read_at' => $notification->read_at,
                    'priority' => 'normal',
                    'status' => 'active',
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                ]);

                $migrated++;
            } catch (\Exception $e) {
                $this->error("Failed to migrate notification {$notification->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Migrated {$migrated} custom notifications");
    }

    protected function migrateEmailNotifications($dryRun = false)
    {
        $this->info('📧 Migrating EmailNotifications...');

        $emailNotifications = EmailNotification::all();
        $this->info("Found {$emailNotifications->count()} email notifications");

        $migrated = 0;
        foreach ($emailNotifications as $notification) {
            if ($dryRun) {
                $this->line("  Would migrate: {$notification->notification_type} (Email ID: {$notification->email_id})");
                $migrated++;
                continue;
            }

            try {
                UnifiedNotification::create([
                    'user_id' => $notification->user_id,
                    'category' => 'email',
                    'type' => $notification->notification_type,
                    'title' => $this->getEmailNotificationTitle($notification->notification_type),
                    'message' => $notification->message,
                    'data' => ['email_id' => $notification->email_id],
                    'email_id' => $notification->email_id,
                    'is_read' => $notification->is_read,
                    'read_at' => $notification->read_at,
                    'priority' => 'normal',
                    'status' => 'active',
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                ]);

                $migrated++;
            } catch (\Exception $e) {
                $this->error("Failed to migrate email notification {$notification->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Migrated {$migrated} email notifications");
    }

    protected function migrateDesignersInboxNotifications($dryRun = false)
    {
        $this->info('📧 Migrating DesignersInboxNotifications...');

        $designersNotifications = DesignersInboxNotification::all();
        $this->info("Found {$designersNotifications->count()} designers inbox notifications");

        $migrated = 0;
        foreach ($designersNotifications as $notification) {
            if ($dryRun) {
                $this->line("  Would migrate: {$notification->title} (Type: {$notification->type})");
                $migrated++;
                continue;
            }

            try {
                UnifiedNotification::create([
                    'user_id' => $notification->user_id,
                    'category' => 'email',
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'email_id' => $notification->email_id,
                    'is_read' => $notification->isRead(),
                    'read_at' => $notification->read_at,
                    'priority' => 'normal',
                    'status' => 'active',
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                ]);

                $migrated++;
            } catch (\Exception $e) {
                $this->error("Failed to migrate designers notification {$notification->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Migrated {$migrated} designers inbox notifications");
    }

    protected function determineCategory($type)
    {
        $taskTypes = [
            'task_assigned', 'task_completed', 'task_overdue', 'task_updated',
            'task_comment', 'task_status_changed', 'task_priority_changed'
        ];

        $emailTypes = [
            'email_reply', 'email_received', 'email_sent', 'email_attachment',
            'email_urgent', 'new_email'
        ];

        if (in_array($type, $taskTypes)) {
            return 'task';
        } elseif (in_array($type, $emailTypes)) {
            return 'email';
        } else {
            // Default to task for unknown types
            return 'task';
        }
    }

    protected function getEmailNotificationTitle($type)
    {
        return match($type) {
            'email_reply' => 'Email Reply Received',
            'email_received' => 'New Email Received',
            'email_sent' => 'Email Sent',
            'email_attachment' => 'Email with Attachment',
            'email_urgent' => 'Urgent Email',
            default => 'Email Notification'
        };
    }
}
