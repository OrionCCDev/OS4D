<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\UnifiedNotification;
use App\Models\DesignersInboxNotification;
use App\Models\CustomNotification;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate notification data and consolidate notifications into the unified system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Starting notification cleanup process...');
        $this->newLine();

        try {
            // Get counts before cleanup
            $unifiedCount = UnifiedNotification::count();
            $designersCount = DesignersInboxNotification::count();
            $customCount = CustomNotification::count();
            
            $this->info('📊 Current notification counts:');
            $this->line("   - Unified Notifications: {$unifiedCount}");
            $this->line("   - Designers Inbox Notifications: {$designersCount}");
            $this->line("   - Custom Notifications: {$customCount}");
            $this->line("   - Total: " . ($unifiedCount + $designersCount + $customCount));
            $this->newLine();
            
            // Migrate Designers Inbox Notifications to Unified Notifications
            $this->info('🔄 Migrating Designers Inbox Notifications to Unified Notifications...');
            
            $designersNotifications = DesignersInboxNotification::whereNull('read_at')->get();
            $migratedCount = 0;
            
            $bar = $this->output->createProgressBar($designersNotifications->count());
            $bar->start();
            
            foreach ($designersNotifications as $notification) {
                // Check if already exists in unified notifications
                $exists = UnifiedNotification::where('user_id', $notification->user_id)
                    ->where('category', 'email')
                    ->where('type', $notification->type)
                    ->where('title', $notification->title)
                    ->where('created_at', $notification->created_at)
                    ->exists();
                    
                if (!$exists) {
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
                    $migratedCount++;
                }
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->line("   ✅ Migrated {$migratedCount} designers inbox notifications");
            $this->newLine();
            
            // Migrate Custom Notifications to Unified Notifications
            $this->info('🔄 Migrating Custom Notifications to Unified Notifications...');
            
            $customNotifications = CustomNotification::where('read', false)->get();
            $migratedCustomCount = 0;
            
            $bar2 = $this->output->createProgressBar($customNotifications->count());
            $bar2->start();
            
            foreach ($customNotifications as $notification) {
                // Determine category based on type
                $category = str_starts_with($notification->type, 'task_') ? 'task' : 'email';
                
                // Check if already exists in unified notifications
                $exists = UnifiedNotification::where('user_id', $notification->user_id)
                    ->where('category', $category)
                    ->where('type', $notification->type)
                    ->where('title', $notification->title)
                    ->where('created_at', $notification->created_at)
                    ->exists();
                    
                if (!$exists) {
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
                    $migratedCustomCount++;
                }
                $bar2->advance();
            }
            
            $bar2->finish();
            $this->newLine();
            $this->line("   ✅ Migrated {$migratedCustomCount} custom notifications");
            $this->newLine();
            
            // Get final counts
            $finalUnifiedCount = UnifiedNotification::count();
            $finalDesignersCount = DesignersInboxNotification::count();
            $finalCustomCount = CustomNotification::count();
            
            $this->info('📊 Final notification counts:');
            $this->line("   - Unified Notifications: {$finalUnifiedCount}");
            $this->line("   - Designers Inbox Notifications: {$finalDesignersCount}");
            $this->line("   - Custom Notifications: {$finalCustomCount}");
            $this->line("   - Total: " . ($finalUnifiedCount + $finalDesignersCount + $finalCustomCount));
            $this->newLine();
            
            $this->info('✅ Notification cleanup completed successfully!');
            $this->info('🎯 All notification systems now use the unified notification system.');
            $this->info('🔔 Notification counts should now be consistent across the application.');
            $this->newLine();
            
            $this->info('📝 Next steps:');
            $this->line('   1. Test the notification system in your browser');
            $this->line('   2. Check that notification counts are now consistent');
            $this->line('   3. If everything works correctly, you can run: php artisan notifications:cleanup-old');
            
        } catch (\Exception $e) {
            $this->error('❌ Error during cleanup: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}