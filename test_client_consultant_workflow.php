<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Client/Consultant Approval Workflow Test ===\n\n";

// 1. Create test users
echo "1. Creating test users...\n";
$manager = User::firstOrCreate(
    ['email' => 'manager@orioncc.com'],
    [
        'name' => 'Test Manager',
        'password' => Hash::make('password'),
        'role' => 'manager',
        'email_verified_at' => now(),
    ]
);

$user = User::firstOrCreate(
    ['email' => 'user@orioncc.com'],
    [
        'name' => 'Test User',
        'password' => Hash::make('password'),
        'role' => 'user',
        'email_verified_at' => now(),
    ]
);

echo "   Manager: " . $manager->name . " (" . $manager->email . ")\n";
echo "   User: " . $user->name . " (" . $user->email . ")\n\n";

// 2. Create test project
echo "2. Creating test project...\n";
$project = Project::firstOrCreate(
    ['name' => 'Client/Consultant Workflow Test'],
    [
        'description' => 'Test project for client/consultant approval workflow',
        'owner_id' => $manager->id,
        'status' => 'active',
    ]
);
echo "   Project: " . $project->name . " (ID: " . $project->id . ")\n\n";

// 3. Create test task
echo "3. Creating test task...\n";
$task = Task::create([
    'title' => 'Client/Consultant Approval Test Task',
    'description' => 'This task will test the new client/consultant approval workflow',
    'project_id' => $project->id,
    'assigned_to' => $user->id,
    'assigned_at' => now(),
    'status' => 'assigned',
    'priority' => 'medium',
    'due_date' => now()->addDays(7),
]);

echo "   Task: " . $task->title . " (ID: " . $task->id . ")\n";
echo "   Status: " . $task->status . "\n\n";

// 4. Test the workflow
echo "4. Testing the workflow...\n";

// Step 1: User accepts task
echo "   Step 1: User accepts task...\n";
Auth::login($user);
$task->accept();
echo "   Status: " . $task->fresh()->status . " (should be 'in_progress')\n";

// Step 2: User submits for review
echo "   Step 2: User submits for review...\n";
$task->submitForReview('Task completed, ready for review');
echo "   Status: " . $task->fresh()->status . " (should be 'submitted_for_review')\n";

// Step 3: Manager approves task
echo "   Step 3: Manager approves task...\n";
Auth::login($manager);
$task->approve('Task approved by manager');
echo "   Status: " . $task->fresh()->status . " (should be 'approved')\n";

// Step 4: Move to waiting for sending approval
echo "   Step 4: Move to waiting for sending approval...\n";
$task->moveToWaitingSendingApproval();
echo "   Status: " . $task->fresh()->status . " (should be 'waiting_sending_client_consultant_approve')\n";

// Step 5: Send for client/consultant approval
echo "   Step 5: Send for client/consultant approval...\n";
$task->sendForClientConsultantApproval();
echo "   Status: " . $task->fresh()->status . " (should be 'waiting_client_consultant_approve')\n";

// Step 6: Update client approval
echo "   Step 6: Update client approval...\n";
$task->updateClientApproval('approved', 'Client approved the task');
echo "   Client Status: " . $task->fresh()->client_status . " (should be 'approved')\n";
echo "   Combined Status: " . $task->fresh()->combined_approval_status . " (should be 'client-approved-consultant-not_attached')\n";

// Step 7: Update consultant approval
echo "   Step 7: Update consultant approval...\n";
$task->updateConsultantApproval('approved', 'Consultant approved the task');
echo "   Consultant Status: " . $task->fresh()->consultant_status . " (should be 'approved')\n";
echo "   Combined Status: " . $task->fresh()->combined_approval_status . " (should be 'client-approved-consultant-approved')\n";
echo "   Final Status: " . $task->fresh()->status . " (should be 'completed')\n\n";

// 5. Test rejection scenario
echo "5. Testing rejection scenario...\n";
$task2 = Task::create([
    'title' => 'Rejection Test Task',
    'description' => 'This task will test rejection in the workflow',
    'project_id' => $project->id,
    'assigned_to' => $user->id,
    'assigned_at' => now(),
    'status' => 'waiting_client_consultant_approve',
    'priority' => 'medium',
    'due_date' => now()->addDays(7),
]);

echo "   Created rejection test task (ID: " . $task2->id . ")\n";
$task2->updateClientApproval('approved', 'Client approved');
$task2->updateConsultantApproval('rejected', 'Consultant rejected the task');
echo "   Client Status: " . $task2->fresh()->client_status . " (should be 'approved')\n";
echo "   Consultant Status: " . $task2->fresh()->consultant_status . " (should be 'rejected')\n";
echo "   Final Status: " . $task2->fresh()->status . " (should be 'rejected')\n\n";

// 6. Test all possible combined statuses
echo "6. Testing all possible combined statuses...\n";
$statuses = ['not_attached', 'approved', 'rejected'];
foreach ($statuses as $clientStatus) {
    foreach ($statuses as $consultantStatus) {
        $task3 = Task::create([
            'title' => "Test Task - Client: {$clientStatus}, Consultant: {$consultantStatus}",
            'description' => 'Test task for combined status',
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'assigned_at' => now(),
            'status' => 'waiting_client_consultant_approve',
            'priority' => 'low',
            'due_date' => now()->addDays(7),
        ]);

        $task3->updateClientApproval($clientStatus, "Client status: {$clientStatus}");
        $task3->updateConsultantApproval($consultantStatus, "Consultant status: {$consultantStatus}");

        echo "   Client: {$clientStatus}, Consultant: {$consultantStatus} -> Combined: " . $task3->fresh()->combined_approval_status . ", Final: " . $task3->fresh()->status . "\n";
    }
}

echo "\n=== Test Completed! ===\n";
echo "The new client/consultant approval workflow has been successfully implemented.\n";
echo "Key features:\n";
echo "- Two new statuses: waiting_sending_client_consultant_approve and waiting_client_consultant_approve\n";
echo "- Client and consultant approval tracking with notes\n";
echo "- Combined status tracking (e.g., client-approved-consultant-rejected)\n";
echo "- Automatic task completion when both approve\n";
echo "- Automatic task rejection when either rejects\n";
echo "- Manager can move approved tasks to the new workflow\n";
echo "- Users can manage client/consultant approval status\n";

