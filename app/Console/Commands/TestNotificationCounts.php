<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\UnifiedNotification;
use App\Models\DesignersInboxNotification;
use App\Models\CustomNotification;
use App\Models\User;
use App\Services\NotificationService;

class TestNotificationCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test notification counts to verify they are consistent across different notification systems';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing notification counts...');
        $this->newLine();

        try {
            // Get counts from all notification systems
            $unifiedCount = UnifiedNotification::where('is_read', false)->where('status', 'active')->count();
            $designersCount = DesignersInboxNotification::whereNull('read_at')->count();
            $customCount = CustomNotification::where('read', false)->count();
            
            $this->info('📊 Notification counts by system:');
            $this->line("   - Unified Notifications (unread): {$unifiedCount}");
            $this->line("   - Designers Inbox Notifications (unread): {$designersCount}");
            $this->line("   - Custom Notifications (unread): {$customCount}");
            $this->line("   - Total unread: " . ($unifiedCount + $designersCount + $customCount));
            $this->newLine();
            
            // Get counts by category in unified system
            $taskUnread = UnifiedNotification::where('category', 'task')->where('is_read', false)->where('status', 'active')->count();
            $emailUnread = UnifiedNotification::where('category', 'email')->where('is_read', false)->where('status', 'active')->count();
            
            $this->info('📊 Unified notification counts by category:');
            $this->line("   - Task notifications (unread): {$taskUnread}");
            $this->line("   - Email notifications (unread): {$emailUnread}");
            $this->line("   - Total unified (unread): " . ($taskUnread + $emailUnread));
            $this->newLine();
            
            // Check for potential duplicates
            $this->info('🔍 Checking for potential duplicates...');
            
            $duplicates = DB::select("
                SELECT user_id, category, type, title, created_at, COUNT(*) as count
                FROM unified_notifications 
                WHERE status = 'active'
                GROUP BY user_id, category, type, title, created_at
                HAVING COUNT(*) > 1
            ");
            
            if (count($duplicates) > 0) {
                $this->warn("   ⚠️  Found " . count($duplicates) . " potential duplicate notifications");
                foreach ($duplicates as $dup) {
                    $this->line("      - User {$dup->user_id}: {$dup->category}/{$dup->type} - {$dup->title} ({$dup->count} copies)");
                }
            } else {
                $this->line("   ✅ No duplicate notifications found");
            }
            
            $this->newLine();
            
            // Test the notification service
            $this->info('🧪 Testing notification service...');
            
            $user = User::first();
            if ($user) {
                $notificationService = new NotificationService();
                $stats = $notificationService->getNotificationStats($user->id);
                
                $this->line("   - User ID: {$user->id}");
                $this->line("   - Total notifications: {$stats['total']}");
                $this->line("   - Unread notifications: {$stats['unread']}");
                $this->line("   - Task unread: {$stats['task_unread']}");
                $this->line("   - Email unread: {$stats['email_unread']}");
                $this->line("   - Read notifications: {$stats['read']}");
            } else {
                $this->warn("   ⚠️  No users found in database");
            }
            
            $this->newLine();
            
            // Summary
            if ($unifiedCount > 0 && $unifiedCount == ($taskUnread + $emailUnread)) {
                $this->info('✅ Notification counts are consistent!');
                $this->info('🎯 The unified notification system is working correctly.');
            } else {
                $this->warn('⚠️  Notification counts may be inconsistent.');
                $this->info('🔧 Consider running: php artisan notifications:cleanup');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error during testing: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}