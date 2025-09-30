<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailNotification;
use App\Models\Email;
use App\Models\User;
use App\Models\Task;

class EmailNotificationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user
        $user = User::first();

        if (!$user) {
            $this->command->info('No users found. Please create a user first.');
            return;
        }

        // Create a test email
        $email = Email::create([
            'from_email' => 'test@example.com',
            'to_email' => 'user@example.com',
            'subject' => 'Test Email Subject',
            'body' => 'This is a test email body',
            'email_type' => 'sent',
            'status' => 'sent',
            'sent_at' => now(),
            'is_tracked' => true,
            'user_id' => $user->id,
        ]);

        // Create test notifications
        EmailNotification::create([
            'user_id' => $user->id,
            'email_id' => $email->id,
            'notification_type' => 'reply_received',
            'message' => 'You received a reply from test@example.com regarding: Test Email Subject',
            'is_read' => false,
        ]);

        EmailNotification::create([
            'user_id' => $user->id,
            'email_id' => $email->id,
            'notification_type' => 'email_opened',
            'message' => 'Your email "Test Email Subject" was opened',
            'is_read' => true,
        ]);

        $this->command->info('Test email notifications created successfully!');
    }
}
