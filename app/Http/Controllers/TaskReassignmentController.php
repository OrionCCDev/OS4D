<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskReassignmentController extends Controller
{
    /**
     * Show the bulk reassignment page for a specific user
     */
    public function showBulkReassignment(User $user)
    {
        // Get all active tasks assigned to this user
        $tasks = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['project', 'folder'])
            ->get();

        // Get all active users who can receive tasks (excluding the current user)
        $availableUsers = User::where('status', 'active')
            ->where('role', '!=', 'admin')
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        return view('tasks.bulk-reassignment', [
            'user' => $user,
            'tasks' => $tasks,
            'availableUsers' => $availableUsers
        ]);
    }

    /**
     * Reassign a single task to another user
     */
    public function reassignTask(Request $request, Task $task)
    {
        $request->validate([
            'new_assigned_to' => 'required|exists:users,id',
            'reassignment_reason' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $oldAssignee = $task->assignee;
            $newAssignee = User::findOrFail($request->new_assigned_to);

            // Update the task
            $task->assigned_to = $request->new_assigned_to;
            $task->save();

            // Create task history entry
            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'action' => 'reassigned',
                'old_value' => $oldAssignee ? $oldAssignee->name : 'Unassigned',
                'new_value' => $newAssignee->name,
                'description' => $request->reassignment_reason ?? 'Task reassigned to ' . $newAssignee->name
            ]);

            $currentUser = auth()->user();

            // Send notification to OLD assignee (if exists and is not the current user)
            if ($oldAssignee && $oldAssignee->id !== $currentUser->id) {
                \App\Models\UnifiedNotification::createTaskNotification(
                    $oldAssignee->id,
                    'task_reassigned_away',
                    'Task Reassigned',
                    'Task "' . $task->title . '" has been reassigned from you to ' . $newAssignee->name . ' by ' . $currentUser->name . ($request->reassignment_reason ? '. Reason: ' . $request->reassignment_reason : ''),
                    [
                        'task_id' => $task->id,
                        'project_id' => $task->project_id,
                        'reassigned_to' => $newAssignee->name,
                        'reason' => $request->reassignment_reason
                    ],
                    $task->id,
                    'normal'
                );
            }

            // Send notification to NEW assignee (if not the current user)
            if ($newAssignee->id !== $currentUser->id) {
                \App\Models\UnifiedNotification::createTaskNotification(
                    $newAssignee->id,
                    'task_assigned',
                    'New Task Assigned to You',
                    'You have been assigned a task: "' . $task->title . '"' . ($oldAssignee ? ' (reassigned from ' . $oldAssignee->name . ')' : '') . ' by ' . $currentUser->name . ($request->reassignment_reason ? '. Reason: ' . $request->reassignment_reason : ''),
                    [
                        'task_id' => $task->id,
                        'project_id' => $task->project_id,
                        'reassigned_from' => $oldAssignee ? $oldAssignee->name : 'Unassigned',
                        'reason' => $request->reassignment_reason,
                        'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null
                    ],
                    $task->id,
                    'high'
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task reassigned successfully to ' . $newAssignee->name
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task reassignment failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reassign task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reassign all tasks from one user to another
     */
    public function bulkReassign(Request $request)
    {
        $request->validate([
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id|different:from_user_id',
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'reassignment_reason' => 'nullable|string|max:500',
            'deactivate_user' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $fromUser = User::findOrFail($request->from_user_id);
            $toUser = User::findOrFail($request->to_user_id);
            $tasks = Task::whereIn('id', $request->task_ids)->get();

            $reassignedCount = 0;

            foreach ($tasks as $task) {
                if ($task->assigned_to == $fromUser->id) {
                    // Update the task
                    $task->assigned_to = $toUser->id;
                    $task->save();

                    // Create task history entry
                    TaskHistory::create([
                        'task_id' => $task->id,
                        'user_id' => auth()->id(),
                        'action' => 'reassigned',
                        'old_value' => $fromUser->name,
                        'new_value' => $toUser->name,
                        'description' => $request->reassignment_reason ?? 'Bulk reassignment from ' . $fromUser->name . ' to ' . $toUser->name
                    ]);

                    // Send notification to NEW assignee (if not the current user)
                    if ($toUser->id !== auth()->id()) {
                        \App\Models\UnifiedNotification::createTaskNotification(
                            $toUser->id,
                            'task_assigned',
                            'New Task Assigned to You',
                            'You have been assigned a task: "' . $task->title . '" (reassigned from ' . $fromUser->name . ') by ' . auth()->user()->name . ($request->reassignment_reason ? '. Reason: ' . $request->reassignment_reason : ''),
                            [
                                'task_id' => $task->id,
                                'project_id' => $task->project_id,
                                'reassigned_from' => $fromUser->name,
                                'reason' => $request->reassignment_reason,
                                'bulk_reassignment' => true,
                                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null
                            ],
                            $task->id,
                            'high'
                        );
                    }

                    $reassignedCount++;
                }
            }

            // Send summary notification to OLD assignee after all tasks are reassigned
            // Only send if the old assignee is not the current user (manager doing the reassignment)
            if ($reassignedCount > 0 && $fromUser->id !== auth()->id()) {
                \App\Models\UnifiedNotification::createTaskNotification(
                    $fromUser->id,
                    'task_reassigned_bulk',
                    'Tasks Reassigned',
                    $reassignedCount . ' task(s) have been reassigned from you to ' . $toUser->name . ' by ' . auth()->user()->name . ($request->reassignment_reason ? '. Reason: ' . $request->reassignment_reason : ''),
                    [
                        'reassigned_to' => $toUser->name,
                        'reassigned_by' => auth()->user()->name,
                        'task_count' => $reassignedCount,
                        'reason' => $request->reassignment_reason
                    ],
                    null,
                    'normal'
                );
            }

            // Optionally deactivate the user
            if ($request->deactivate_user) {
                $fromUser->status = 'inactive';
                $fromUser->deactivated_at = now();
                $fromUser->deactivation_reason = $request->reassignment_reason ?? 'User deactivated and tasks reassigned';
                $fromUser->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $reassignedCount . ' task(s) reassigned successfully from ' . $fromUser->name . ' to ' . $toUser->name,
                'reassigned_count' => $reassignedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk task reassignment failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reassign tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user status (activate/deactivate/resign)
     */
    public function updateUserStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,resigned',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $user->status = $request->status;

            if (in_array($request->status, ['inactive', 'resigned'])) {
                $user->deactivated_at = now();
                $user->deactivation_reason = $request->reason;
            } else {
                $user->deactivated_at = null;
                $user->deactivation_reason = null;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('User status update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's active tasks count
     */
    public function getUserActiveTasks(User $user)
    {
        $activeTasks = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $completedTasks = Task::where('assigned_to', $user->id)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'active_tasks' => $activeTasks,
            'completed_tasks' => $completedTasks,
            'total_tasks' => $activeTasks + $completedTasks
        ]);
    }
}
