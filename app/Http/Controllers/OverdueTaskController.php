<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\TaskOverdueReminderMail;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OverdueTaskController extends Controller
{
    /**
     * Display list of overdue tasks.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isManagerView = $user->isManager();

        $query = $this->baseQuery($user);

        if ($isManagerView && $request->filled('assigned_to')) {
            $query->where('assigned_to', $request->integer('assigned_to'));
        }

        if ($request->filled('project')) {
            $project = trim($request->get('project'));
            $query->whereHas('project', function ($q) use ($project) {
                $q->where('name', 'like', '%' . $project . '%')
                    ->orWhere('short_code', 'like', '%' . $project . '%');
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('reference', 'like', '%' . $search . '%')
                    ->orWhere('task_code', 'like', '%' . $search . '%');
            });
        }

        $tasks = $query
            ->with(['assignee:id,name,email', 'project:id,name,short_code'])
            ->orderBy('due_date', 'asc')
            ->paginate(20)
            ->withQueryString();

        $filters = [
            'search' => $request->get('search'),
            'assigned_to' => $request->get('assigned_to'),
            'project' => $request->get('project'),
        ];

        $users = $isManagerView
            ? User::active()
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('tasks.overdue', [
            'tasks' => $tasks,
            'filters' => $filters,
            'users' => $users,
            'isManagerView' => $isManagerView,
        ]);
    }

    /**
     * Provide a lightweight summary of overdue tasks for badges.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $this->baseQuery($user)->count();

        return response()->json([
            'success' => true,
            'count' => $count,
            'manager' => $user->isManager(),
        ]);
    }

    /**
     * Return paginated overdue task data for the navbar modal or other async consumers.
     */
    public function list(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        $query = $this->baseQuery($user)
            ->with(['assignee:id,name,email', 'project:id,name,short_code'])
            ->orderBy('due_date', 'asc');

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage);

        $tasks = $paginator->getCollection()->map(function (Task $task) use ($user) {
            $dueDate = $task->due_date ? $task->due_date->copy()->setTimezone(config('app.timezone', 'UTC')) : null;
            $overdueDuration = $dueDate ? $this->formatOverdueDuration($dueDate) : null;

            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'assignee' => optional($task->assignee)->name,
                'assignee_email' => optional($task->assignee)->email,
                'project' => optional($task->project)->name,
                'project_code' => optional($task->project)->short_code,
                'due_date' => $dueDate ? $dueDate->toDateTimeString() : null,
                'due_date_for_humans' => $dueDate ? $dueDate->format('M d, Y H:i') : 'Not set',
                'overdue_duration' => $overdueDuration,
                'show_url' => route('tasks.show', $task->id),
                'edit_url' => $user->isManager() ? route('tasks.edit', $task->id) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'manager' => $user->isManager(),
            'data' => $tasks,
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ]);
    }

    /**
     * Send an overdue reminder email to the task assignee.
     */
    public function sendDelayEmail(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Only managers can send overdue reminders.',
            ], 403);
        }

        if (!$task->assignee) {
            return response()->json([
                'success' => false,
                'message' => 'Task has no assigned user.',
            ], 422);
        }

        if (!$this->isTaskOverdue($task)) {
            return response()->json([
                'success' => false,
                'message' => 'Task is no longer overdue.',
            ], 422);
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:2000',
        ]);

        try {
            $task->loadMissing(['assignee', 'project']);
            Mail::to($task->assignee->email)
                ->send(new TaskOverdueReminderMail($task, $user, $validated['message'] ?? null));

            Log::info('Overdue reminder email sent', [
                'task_id' => $task->id,
                'sent_by' => $user->id,
                'sent_to' => $task->assignee->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Overdue reminder email sent successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send overdue reminder email', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminder email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build the base overdue query for manager/user contexts.
     */
    private function baseQuery(User $user)
    {
        $query = Task::query()
            ->whereNotNull('due_date')
            ->whereNotIn('status', [
                'completed',
                'archived',
                'cancelled',
                'declined',
            ])
            ->whereDate('due_date', '<', now()->startOfDay())
            ->whereDoesntHave('emailPreparations', function ($q) {
                $q->where('status', 'sent')
                    ->whereNotNull('sent_at');
            });

        if (!$user->isManager()) {
            $query->where('assigned_to', $user->id);
        }

        return $query;
    }

    /**
     * Format overdue duration into a human readable string.
     */
    private function formatOverdueDuration(Carbon $dueDate): string
    {
        $now = now();
        if ($dueDate->gte($now)) {
            return 'Due soon';
        }

        $diff = $dueDate->diff($now);

        $parts = [];
        if ($diff->d > 0) {
            $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
        }
        if ($diff->d < 1 && $diff->i > 0) {
            $parts[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        }

        if (empty($parts)) {
            $parts[] = 'less than 1 hour';
        }

        return implode(' ', $parts);
    }

    /**
     * Determine if the given task still meets overdue criteria.
     */
    private function isTaskOverdue(Task $task): bool
    {
        if (!$task->due_date) {
            return false;
        }

        if ($task->due_date->gte(now()->startOfDay())) {
            return false;
        }

        if (!$task->emailPreparations()
            ->where('status', 'sent')
            ->whereNotNull('sent_at')
            ->exists()) {
            return true;
        }

        return false;
    }
}

