<?php

namespace App\Console\Commands;

use App\Services\LiveEmailMonitoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LiveEmailMonitorCommand extends Command
{
    protected $signature = 'email:live-monitor {--test : Test mode without actual IMAP connection}';
    protected $description = 'Monitor engineering@orion-contracting.com inbox for live email notifications';

    public function handle()
    {
        $this->info('🔍 Starting live email monitoring...');

        try {
            $monitoringService = app(LiveEmailMonitoringService::class);

            if ($this->option('test')) {
                $this->info('🧪 Running in test mode...');
                $this->testMode();
                return;
            }

            $results = $monitoringService->monitorInbox();

            $this->displayResults($results);

            // Log results
            Log::info('Live email monitoring completed', $results);

        } catch (\Exception $e) {
            $this->error('❌ Error during monitoring: ' . $e->getMessage());
            Log::error('Live email monitoring error: ' . $e->getMessage());
        }
    }

    protected function testMode()
    {
        $this->info('📧 Creating test email notifications...');

        // Create test notifications for different scenarios
        $this->createTestNotifications();

        $this->info('✅ Test notifications created successfully!');
    }

    protected function createTestNotifications()
    {
        // Get all users
        $users = \App\Models\User::all();
        $manager = \App\Models\User::find(1);

        foreach ($users as $user) {
            // Create test email
            $email = \App\Models\Email::create([
                'user_id' => $user->id,
                'from_email' => 'test@example.com',
                'to_email' => $user->email,
                'subject' => 'Test Email - ' . now()->format('H:i:s'),
                'body' => 'This is a test email for live monitoring.',
                'email_type' => 'received',
                'status' => 'received',
                'is_tracked' => false,
                'received_at' => now(),
                'gmail_message_id' => 'test_' . uniqid(),
            ]);

            // Create notification for user
            \App\Models\EmailNotification::create([
                'user_id' => $user->id,
                'email_id' => $email->id,
                'notification_type' => 'email_received',
                'message' => "New email received from test@example.com: Test Email - " . now()->format('H:i:s'),
                'is_read' => false,
            ]);

            // Also create notification for manager
            if ($manager && $manager->id !== $user->id) {
                \App\Models\EmailNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $email->id,
                    'notification_type' => 'email_received',
                    'message' => "New email received for {$user->name}: Test Email - " . now()->format('H:i:s'),
                    'is_read' => false,
                ]);
            }
        }
    }

    protected function displayResults(array $results)
    {
        $this->info('📊 Monitoring Results:');
        $this->line('   📧 New emails: ' . $results['new_emails']);
        $this->line('   💬 Replies: ' . $results['replies']);
        $this->line('   🔔 Notifications created: ' . $results['notifications_created']);

        if (!empty($results['errors'])) {
            $this->warn('⚠️  Errors:');
            foreach ($results['errors'] as $error) {
                $this->line('   - ' . $error);
            }
        }

        if ($results['notifications_created'] > 0) {
            $this->info('✅ Live notifications sent successfully!');
        } else {
            $this->info('ℹ️  No new emails found.');
        }
    }
}
