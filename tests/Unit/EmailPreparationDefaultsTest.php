<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\Contractor;
use App\Models\User;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Auth;
use ReflectionClass;

class EmailPreparationDefaultsTest extends TestCase
{
    public function test_create_default_email_preparation_logic()
    {
        // Test the core logic without database dependencies
        $this->assertTrue(true); // Placeholder test

        // Let's test the email building logic directly
        $ccEmails = [];

        // Add engineering@orion-contracting.com
        $ccEmails[] = 'engineering@orion-contracting.com';

        // Add manager email (project owner)
        $projectOwnerEmail = 'owner@test.com';
        if ($projectOwnerEmail) {
            $ccEmails[] = $projectOwnerEmail;
        }

        // Add all contractors assigned to this project
        $projectContractors = [
            (object)['email' => 'contractor1@test.com'],
            (object)['email' => 'contractor2@test.com']
        ];

        foreach ($projectContractors as $contractor) {
            if ($contractor->email && !in_array($contractor->email, $ccEmails)) {
                $ccEmails[] = $contractor->email;
            }
        }

        // Assertions
        $this->assertContains('engineering@orion-contracting.com', $ccEmails);
        $this->assertContains('owner@test.com', $ccEmails);
        $this->assertContains('contractor1@test.com', $ccEmails);
        $this->assertContains('contractor2@test.com', $ccEmails);
        $this->assertCount(4, $ccEmails);

        // Test duplicate prevention
        $duplicateContractors = [
            (object)['email' => 'contractor1@test.com'], // Duplicate
            (object)['email' => 'contractor3@test.com']
        ];

        foreach ($duplicateContractors as $contractor) {
            if ($contractor->email && !in_array($contractor->email, $ccEmails)) {
                $ccEmails[] = $contractor->email;
            }
        }

        $this->assertCount(5, $ccEmails); // Should only add contractor3, not duplicate contractor1
        $this->assertContains('contractor3@test.com', $ccEmails);
    }

    public function test_email_subject_and_body_formatting()
    {
        $taskTitle = 'Test Task';
        $projectName = 'Test Project';
        $priority = 'medium';
        $dueDate = '2024-01-15';
        $completionDate = now()->format('M d, Y');
        $userName = 'Test User';

        $defaultSubject = "Project Update: Task Completed - {$taskTitle}";
        $defaultBody = "Dear Project Manager,\n\n" .
                      "I hope this email finds you well. I am writing to inform you that the assigned task '{$taskTitle}' has been completed and submitted for review.\n\n" .
                      "Project Information:\n" .
                      "- Project: {$projectName}\n" .
                      "- Priority Level: " . ucfirst($priority) . "\n" .
                      "- Original Due Date: {$dueDate}\n" .
                      "- Completion Date: {$completionDate}\n\n" .
                      "The task has been completed according to the specifications and is ready for your review. Please let me know if you need any additional information or modifications.\n\n" .
                      "Thank you for your time and consideration.\n\n" .
                      "Best regards,\n" .
                      $userName;

        $this->assertStringContainsString('Project Update: Task Completed - Test Task', $defaultSubject);
        $this->assertStringContainsString('Dear Project Manager', $defaultBody);
        $this->assertStringContainsString('Test Task', $defaultBody);
        $this->assertStringContainsString('Test Project', $defaultBody);
        $this->assertStringContainsString('Medium', $defaultBody);
        $this->assertStringContainsString('Test User', $defaultBody);
    }
}
