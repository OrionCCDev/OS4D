<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Contractor;

// Example usage of the new relationships

echo "=== RELATIONSHIP SYSTEM DEMONSTRATION ===\n\n";

// 1. Create a project and add users to it
echo "1. PROJECT-USER RELATIONSHIPS:\n";
echo "----------------------------------------\n";

$project = Project::first(); // Get existing project
$user1 = User::where('role', 'manager')->first();
$user2 = User::where('role', 'user')->first();

if ($project && $user1 && $user2) {
    // Add users to project with different roles
    $project->addUser($user1, 'lead');
    $project->addUser($user2, 'member');

    echo "✓ Added {$user1->name} as lead to project: {$project->name}\n";
    echo "✓ Added {$user2->name} as member to project: {$project->name}\n";

    // Get all project members
    $members = $project->users;
    echo "✓ Project has " . $members->count() . " members\n";

    // Get only leads
    $leads = $project->leads;
    echo "✓ Project has " . $leads->count() . " leads\n";
}

echo "\n";

// 2. Create contractors and add them to tasks
echo "2. TASK-CONTRACTOR RELATIONSHIPS:\n";
echo "----------------------------------------\n";

// Create sample contractors
$client = Contractor::create([
    'name' => 'John Client',
    'email' => 'john.client@example.com',
    'mobile' => '+1234567890',
    'position' => 'Project Manager',
    'company_name' => 'Client Corp',
    'type' => 'client'
]);

$consultant = Contractor::create([
    'name' => 'Jane Consultant',
    'email' => 'jane.consultant@example.com',
    'mobile' => '+1234567891',
    'position' => 'Senior Consultant',
    'company_name' => 'Consulting Inc',
    'type' => 'consultant'
]);

echo "✓ Created client: {$client->name}\n";
echo "✓ Created consultant: {$consultant->name}\n";

// Get a task and add contractors to it
$task = Task::first();
if ($task) {
    $task->addContractor($client, 'participant');
    $task->addContractor($consultant, 'reviewer');

    echo "✓ Added contractors to task: {$task->title}\n";

    // Get all contractors for this task
    $contractors = $task->contractors;
    echo "✓ Task has " . $contractors->count() . " contractors\n";

    // Get only clients
    $clients = $task->clients;
    echo "✓ Task has " . $clients->count() . " clients\n";

    // Get only consultants
    $consultants = $task->consultants;
    echo "✓ Task has " . $consultants->count() . " consultants\n";
}

echo "\n";

// 3. Query approved tasks for contractors
echo "3. APPROVED TASKS QUERIES:\n";
echo "----------------------------------------\n";

// Get approved tasks for all clients
$approvedClientTasks = Task::getApprovedTasksForContractorType('client');
echo "✓ Found " . $approvedClientTasks->count() . " approved tasks for clients\n";

// Get approved tasks for all consultants
$approvedConsultantTasks = Task::getApprovedTasksForContractorType('consultant');
echo "✓ Found " . $approvedConsultantTasks->count() . " approved tasks for consultants\n";

// Get approved tasks for specific client on specific project
if ($task && $project) {
    $clientTasksOnProject = Task::getApprovedTasksForContractor($client, $project->id);
    echo "✓ Found " . $clientTasksOnProject->count() . " approved tasks for {$client->name} on project {$project->name}\n";
}

// Get approved tasks for specific consultant
$consultantTasks = $consultant->approvedTasks;
echo "✓ {$consultant->name} has " . $consultantTasks->count() . " approved tasks\n";

echo "\n";

// 4. User can manage contractors on tasks
echo "4. MANAGER FUNCTIONS:\n";
echo "----------------------------------------\n";

if ($task && $user1 && $user1->isManager()) {
    echo "✓ User {$user1->name} is a manager and can:\n";
    echo "  - Add contractors to tasks\n";
    echo "  - Remove contractors from tasks\n";
    echo "  - Change contractor roles on tasks\n";
    echo "  - View all project members\n";
    echo "  - Manage project user roles\n";
}

echo "\n";

// 5. Complex queries examples
echo "5. COMPLEX QUERY EXAMPLES:\n";
echo "----------------------------------------\n";

// Get all tasks that have both clients and consultants
$tasksWithBothTypes = Task::whereHas('contractors', function($q) {
    $q->where('type', 'client');
})->whereHas('contractors', function($q) {
    $q->where('type', 'consultant');
})->get();

echo "✓ Found " . $tasksWithBothTypes->count() . " tasks with both clients and consultants\n";

// Get all projects where a specific user is a lead
if ($user1) {
    $userLeadProjects = $user1->projectsAsLead;
    echo "✓ {$user1->name} is a lead on " . $userLeadProjects->count() . " projects\n";
}

// Get all contractors of a specific type
$allClients = Contractor::clients()->get();
$allConsultants = Contractor::consultants()->get();
echo "✓ Total clients in system: " . $allClients->count() . "\n";
echo "✓ Total consultants in system: " . $allConsultants->count() . "\n";

echo "\n=== RELATIONSHIP SYSTEM READY ===\n";

// Summary of what we've implemented:
echo "\nSUMMARY OF IMPLEMENTED FEATURES:\n";
echo "================================\n";
echo "✓ Many-to-many relationship between Users and Projects\n";
echo "✓ Many-to-many relationship between Tasks and Contractors\n";
echo "✓ Contractors table with client/consultant types\n";
echo "✓ Mobile and position fields added to Users table\n";
echo "✓ User and Manager can add/change contractors on tasks\n";
echo "✓ Query approved tasks for clients on projects\n";
echo "✓ Query approved tasks for consultants on projects\n";
echo "✓ Project can have many users with different roles\n";
echo "✓ Task belongs to one project, project has many tasks\n";
echo "✓ All relationships with proper pivot tables\n";
echo "✓ Helper methods for managing relationships\n";

echo "\nYou can now:\n";
echo "- Add users to projects with roles (member, lead, observer)\n";
echo "- Add contractors to tasks with roles (participant, reviewer, stakeholder)\n";
echo "- Query approved tasks by contractor type and project\n";
echo "- Manage all relationships through the model methods\n";
