<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\UnifiedNotification;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function showReassignmentDebug()
    {
        // Get the most recent notifications from the last hour
        $recentNotifications = UnifiedNotification::where('created_at', '>=', now()->subHour())
            ->orderBy('created_at', 'desc')
            ->get();

        $debugInfo = [
            'total_notifications' => $recentNotifications->count(),
            'notifications' => []
        ];

        foreach ($recentNotifications as $notif) {
            $user = User::find($notif->user_id);
            $debugInfo['notifications'][] = [
                'id' => $notif->id,
                'user_id' => $notif->user_id,
                'user_name' => $user ? $user->name : 'Unknown',
                'type' => $notif->type,
                'title' => $notif->title,
                'message' => $notif->message,
                'created_at' => $notif->created_at->format('Y-m-d H:i:s'),
                'task_id' => $notif->task_id,
                'is_reassignment' => strpos($notif->message, 'reassigned from') !== false
            ];
        }

        return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
    }

    public function testReassignment(Request $request)
    {
        // This will simulate a reassignment and show us what happens
        $taskId = $request->input('task_id', 3); // Default to task 3
        $fromUserId = $request->input('from_user_id');
        $toUserId = $request->input('to_user_id');

        if (!$fromUserId || !$toUserId) {
            return response()->json([
                'error' => 'Please provide from_user_id and to_user_id parameters'
            ], 400);
        }

        $task = Task::find($taskId);
        $fromUser = User::find($fromUserId);
        $toUser = User::find($toUserId);

        if (!$task || !$fromUser || !$toUser) {
            return response()->json([
                'error' => 'Task or users not found'
            ], 404);
        }

        // Simulate the reassignment logic
        $oldAssignee = $task->assignee;
        $isReassignment = $task->assigned_to != $toUserId;

        return response()->json([
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'current_assignee_id' => $task->assigned_to,
                'current_assignee_name' => $oldAssignee ? $oldAssignee->name : 'None'
            ],
            'from_user' => [
                'id' => $fromUser->id,
                'name' => $fromUser->name
            ],
            'to_user' => [
                'id' => $toUser->id,
                'name' => $toUser->name
            ],
            'is_reassignment' => $isReassignment,
            'would_send_notifications' => [
                'to_old_assignee' => $oldAssignee && $oldAssignee->id != auth()->id(),
                'to_new_assignee' => $toUser->id != auth()->id()
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
