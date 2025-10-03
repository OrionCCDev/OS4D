<?php

namespace App\Console\Commands;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DetectSentEmailsCommand extends Command
{
    protected $signature = 'email:detect-sent';
    protected $description = 'Automatically detect sent emails and create notifications';

    public function handle()
    {
        Log::info('🔍 Starting automatic email detection...');

        try {
            $this->detectSentEmails();

        } catch (\Exception $e) {
            Log::error('❌ Error during automatic email detection: ' . $e->getMessage());
        }
    }

    protected function testMode()
    {
        $this->info('🧪 Running in test mode - creating notifications for existing sent emails...');

        // Get all sent emails from the last 24 hours
        $sentEmails = Email::where('email_type', 'sent')
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $this->info("Found {$sentEmails->count()} sent emails in the last 24 hours");

        foreach ($sentEmails as $email) {
            $this->createNotificationsForSentEmail($email);
        }

        $this->info('✅ Test notifications created successfully!');
    }

    protected function detectSentEmails()
    {
        // Get all sent emails from the last 24 hours that don't have notifications yet
        $sentEmails = Email::where('email_type', 'sent')
            ->where('created_at', '>=', now()->subHours(24))
            ->whereDoesntHave('notifications')
            ->get();

        Log::info("Found {$sentEmails->count()} sent emails without notifications");

        $notificationsCreated = 0;

        foreach ($sentEmails as $email) {
            $created = $this->createNotificationsForSentEmail($email);
            if ($created) {
                $notificationsCreated++;
            }
        }

        Log::info("✅ Automatic email detection completed: {$notificationsCreated} notifications created");
    }

    protected function createNotificationsForSentEmail(Email $email): bool
    {
        try {
            $user = User::find($email->user_id);
            if (!$user) {
                Log::warning("User not found for email ID: {$email->id}");
                return false;
            }

            // Check if notification already exists for the sender
            $existingNotification = EmailNotification::where('user_id', $user->id)
                ->where('email_id', $email->id)
                ->where('notification_type', 'email_sent')
                ->first();

            if ($existingNotification) {
                Log::info("Email sent notification already exists for user {$user->id}, email ID: {$email->id}");
                return false; // Already exists
            }

            // Create notification for original sender
            $notification = EmailNotification::create([
                'user_id' => $user->id,
                'email_id' => $email->id,
                'notification_type' => 'email_sent',
                'message' => "Email sent successfully to {$email->to_email}: {$email->subject}",
                'is_read' => false,
            ]);

            Log::info("✅ Created notification for user {$user->id}: {$email->subject}");

            // ALSO create notification for manager (User ID 1)
            $manager = User::find(1);
            if ($manager && $manager->id !== $user->id) {
                // Check if manager notification already exists
                $existingManagerNotification = EmailNotification::where('user_id', $manager->id)
                    ->where('email_id', $email->id)
                    ->where('notification_type', 'email_sent')
                    ->first();

                if (!$existingManagerNotification) {
                    $managerNotification = EmailNotification::create([
                        'user_id' => $manager->id,
                        'email_id' => $email->id,
                        'notification_type' => 'email_sent',
                        'message' => "Email sent by {$user->name} to {$email->to_email}: {$email->subject}",
                        'is_read' => false,
                    ]);

                    Log::info("✅ Created manager notification: {$email->subject}");
                } else {
                    Log::info("Manager notification already exists for email ID: {$email->id}");
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Error creating notifications for email {$email->id}: " . $e->getMessage());
            return false;
        }
    }

    protected function createSimulatedReplyNotification(Email $email, User $user)
    {
        try {
            // Wait a bit to simulate time passing
            sleep(1);

            // Create a simulated reply email
            $replyEmail = Email::create([
                'user_id' => $user->id,
                'from_email' => 'reply-sender@example.com',
                'to_email' => $user->email,
                'subject' => 'Re: ' . $email->subject,
                'body' => 'This is a simulated reply to your email: ' . $email->subject,
                'email_type' => 'received',
                'status' => 'received',
                'is_tracked' => false,
                'received_at' => now(),
                'reply_to_email_id' => $email->id,
                'thread_id' => $email->thread_id ?? 'simulated_thread_' . $email->id,
                'gmail_message_id' => 'simulated_reply_' . uniqid(),
            ]);

            // Create reply notification for original sender
            $replyNotification = EmailNotification::create([
                'user_id' => $user->id,
                'email_id' => $email->id,
                'notification_type' => 'reply_received',
                'message' => "You received a reply from reply-sender@example.com regarding: {$email->subject}",
                'is_read' => false,
            ]);

            $this->line("✅ Created simulated reply notification for user {$user->id}");

            // ALSO create reply notification for manager
            $manager = User::find(1);
            if ($manager && $manager->id !== $user->id) {
                $managerReplyNotification = EmailNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $email->id,
                    'notification_type' => 'reply_received',
                    'message' => "Reply received from reply-sender@example.com for email: {$email->subject} (sent by {$user->name})",
                    'is_read' => false,
                ]);

                $this->line("✅ Created manager reply notification");
            }

        } catch (\Exception $e) {
            $this->error("Error creating simulated reply notification: " . $e->getMessage());
            Log::error("Error creating simulated reply notification: " . $e->getMessage());
        }
    }
}
