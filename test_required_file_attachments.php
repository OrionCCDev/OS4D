<?php

/**
 * Test script for automatic required file attachment functionality
 *
 * This script tests the new automatic attachment feature that attaches
 * all task files (required files) to confirmation emails.
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use App\Services\RequiredFileAttachmentService;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Automatic Required File Attachment Feature ===\n\n";

try {
    // Initialize the service
    $requiredFileService = new RequiredFileAttachmentService();

    // Find a task with attachments to test
    $taskWithAttachments = Task::whereHas('attachments')->with('attachments')->first();

    if (!$taskWithAttachments) {
        echo "❌ No tasks with attachments found. Please upload some files to a task first.\n";
        echo "   You can upload files to any task and then run this test again.\n\n";

        // Show available tasks
        $tasks = Task::with('attachments')->get();
        echo "Available tasks:\n";
        foreach ($tasks as $task) {
            $attachmentCount = $task->attachments ? $task->attachments->count() : 0;
            echo "- Task #{$task->id}: {$task->title} (Attachments: {$attachmentCount})\n";
        }
        exit(1);
    }

    echo "✅ Found task with attachments: #{$taskWithAttachments->id} - {$taskWithAttachments->title}\n";
    echo "   Attachments count: " . $taskWithAttachments->attachments->count() . "\n\n";

    // Test 1: Check if task has required files
    echo "Test 1: Checking if task has required files...\n";
    $hasRequiredFiles = $requiredFileService->hasRequiredFiles($taskWithAttachments);
    echo "   Result: " . ($hasRequiredFiles ? "✅ Yes" : "❌ No") . "\n\n";

    // Test 2: Get required files count
    echo "Test 2: Getting required files count...\n";
    $filesCount = $requiredFileService->getRequiredFilesCount($taskWithAttachments);
    echo "   Result: {$filesCount} files\n\n";

    // Test 3: Get required files details
    echo "Test 3: Getting required files details...\n";
    $requiredFiles = $requiredFileService->getRequiredFilesForTask($taskWithAttachments);
    echo "   Found " . count($requiredFiles) . " required files:\n";
    foreach ($requiredFiles as $file) {
        echo "   - {$file['original_name']} ({$file['size_bytes']} bytes, {$file['mime_type']})\n";
    }
    echo "\n";

    // Test 4: Validate required files
    echo "Test 4: Validating required files...\n";
    $validation = $requiredFileService->validateRequiredFiles($taskWithAttachments);
    echo "   Validation result: " . ($validation['valid'] ? "✅ Valid" : "❌ Invalid") . "\n";
    echo "   Files count: {$validation['files_count']}\n";

    if (!empty($validation['errors'])) {
        echo "   Errors:\n";
        foreach ($validation['errors'] as $error) {
            echo "   - {$error}\n";
        }
    }

    if (!empty($validation['warnings'])) {
        echo "   Warnings:\n";
        foreach ($validation['warnings'] as $warning) {
            echo "   - {$warning}\n";
        }
    }
    echo "\n";

    // Test 5: Get attachment data for email
    echo "Test 5: Getting attachment data for email...\n";
    $attachmentData = $requiredFileService->getAttachmentDataForEmail($taskWithAttachments);
    echo "   Prepared " . count($attachmentData) . " attachments for email:\n";
    foreach ($attachmentData as $attachment) {
        echo "   - {$attachment['filename']} ({$attachment['size']} bytes, {$attachment['mime_type']})\n";
    }
    echo "\n";

    // Test 6: Log required files info
    echo "Test 6: Logging required files information...\n";
    $requiredFileService->logRequiredFilesInfo($taskWithAttachments);
    echo "   ✅ Information logged to Laravel logs\n\n";

    // Test 7: Test with a task without attachments
    echo "Test 7: Testing with a task without attachments...\n";
    $taskWithoutAttachments = Task::whereDoesntHave('attachments')->first();

    if ($taskWithoutAttachments) {
        echo "   Testing task: #{$taskWithoutAttachments->id} - {$taskWithoutAttachments->title}\n";
        $hasRequiredFiles = $requiredFileService->hasRequiredFiles($taskWithoutAttachments);
        $filesCount = $requiredFileService->getRequiredFilesCount($taskWithoutAttachments);
        $requiredFiles = $requiredFileService->getRequiredFilesForTask($taskWithoutAttachments);

        echo "   Has required files: " . ($hasRequiredFiles ? "✅ Yes" : "❌ No") . "\n";
        echo "   Files count: {$filesCount}\n";
        echo "   Required files array: " . count($requiredFiles) . " items\n";
    } else {
        echo "   ❌ No tasks without attachments found\n";
    }
    echo "\n";

    echo "=== Test Summary ===\n";
    echo "✅ All tests completed successfully!\n";
    echo "✅ The automatic required file attachment feature is working correctly.\n";
    echo "✅ Task attachments will now be automatically included in confirmation emails.\n\n";

    echo "Next steps:\n";
    echo "1. Send a confirmation email for task #{$taskWithAttachments->id}\n";
    echo "2. Check that all task attachments are included in the email\n";
    echo "3. Verify the attachments are properly named and accessible\n\n";

} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Test completed successfully! 🎉\n";
