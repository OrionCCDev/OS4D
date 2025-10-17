<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailSignatureService;
use App\Models\User;

class TestEmailSignature extends Command
{
    protected $signature = 'test:email-signature {user_id? : User ID to test signature for}';
    protected $description = 'Test email signature generation for a user';

    protected $signatureService;

    public function __construct(EmailSignatureService $signatureService)
    {
        parent::__construct();
        $this->signatureService = $signatureService;
    }

    public function handle()
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return 1;
            }
            $this->testUserSignature($user);
        } else {
            // Test with all users
            $users = User::take(5)->get();
            foreach ($users as $user) {
                $this->testUserSignature($user);
                $this->line('---');
            }
        }

        return 0;
    }

    protected function testUserSignature(User $user)
    {
        $this->info("Testing signature for: {$user->name} ({$user->email})");
        $this->info("Role: {$user->role}");
        $this->info("Mobile: " . ($user->mobile ?? $user->phone ?? 'Not set'));

        // Test HTML signature
        $htmlSignature = $this->signatureService->generateSignature($user, 'blue');
        $this->info("HTML Signature (Blue Logo):");
        $this->line($htmlSignature);

        // Test plain text signature
        $textSignature = $this->signatureService->generatePlainTextSignature($user);
        $this->info("Plain Text Signature:");
        $this->line($textSignature);
    }
}
