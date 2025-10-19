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
        $tasks = Task::where('assignee_id', $user->id)
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
            'new_assignee_id' => 'required|exists:users,id',
            'reassignment_reason' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $oldAssignee = $task->assignee;
            $newAssignee = User::findOrFail($request->new_assignee_id);

            // Update the task
            $task->assignee_id = $request->new_assignee_id;
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

            // Send notification to new assignee
            $task->sendNotification(
                $newAssignee->id,
                'task_assigned',
                'New Task Assigned',
                'You have been assigned a task: ' . $task->title
            );

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
                if ($task->assignee_id == $fromUser->id) {
                    // Update the task
                    $task->assignee_id = $toUser->id;
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

                    // Send notification to new assignee
                    $task->sendNotification(
                        $toUser->id,
                        'task_assigned',
                        'New Task Assigned',
                        'You have been assigned a task: ' . $task->title
                    );

                    $reassignedCount++;
                }
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
        $activeTasks = Task::where('assignee_id', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $completedTasks = Task::where('assignee_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'active_tasks' => $activeTasks,
            'completed_tasks' => $completedTasks,
            'total_tasks' => $activeTasks + $completedTasks
        ]);
    }
}
