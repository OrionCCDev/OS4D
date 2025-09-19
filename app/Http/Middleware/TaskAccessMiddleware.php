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

        // Managers and admins have full access
        if ($user && $user->isManager()) {
            return $next($request);
        }

        // For task-related routes, check if user is assigned to the task
        if ($request->route('task')) {
            $task = $request->route('task');

            // If task is a model instance, check assignment
            if ($task instanceof Task) {
                if ($task->assigned_to !== $user->id) {
                    abort(403, 'Access denied. You can only access tasks assigned to you.');
                }
            }
        }

        return $next($request);
    }
}
