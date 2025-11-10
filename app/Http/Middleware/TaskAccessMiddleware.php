<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Task;

class TaskAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Managers (including sub-admins) need special handling
            if ($user->isManager()) {
                $taskParam = $request->route('task');

                if ($user->isSubAdmin() && $taskParam) {
                    $task = $taskParam instanceof Task ? $taskParam : Task::find($taskParam);

                    if ($task) {
                        if ($task->created_by !== $user->id && $task->assigned_to !== $user->id) {
                            abort(403, 'Access denied. Sub-admins can only access tasks they created or are assigned to.');
                        }
                    }
                }

                return $next($request);
            }

            // Regular users: ensure task assignment
            if ($request->route('task')) {
                $taskParam = $request->route('task');
                $task = $taskParam instanceof Task ? $taskParam : Task::find($taskParam);

                if ($task && $task->assigned_to !== $user->id) {
                    abort(403, 'Access denied. You can only access tasks assigned to you.');
                }
            }

            return $next($request);
        }

        abort(403, 'Access denied.');
    }
}
