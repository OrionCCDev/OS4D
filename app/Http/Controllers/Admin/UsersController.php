<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function index(): View
    {
        $users = User::latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('img')) {
            $dir = public_path('uploads/users');
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            $file = $request->file('img');
            $filename = uniqid('u_').'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $data['img'] = $filename;
        }

        $user = User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'User created successfully');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('img')) {
            try {
                // Delete old image if it's not default
                // Never delete default.png, default.jpg, 1.png, default_user.jpg, or default-user.jpg
                $old = $user->img;
                if ($old && !in_array($old, ['default.png', 'default.jpg', '1.png', 'default_user.jpg', 'default-user.jpg'])) {
                    $oldPath = public_path('uploads/users/'.$old);
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $dir = public_path('uploads/users');
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                
                $file = $request->file('img');
                $filename = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($dir, $filename);
                $data['img'] = $filename;
                
                \Log::info('User image uploaded successfully', [
                    'user_id' => $user->id,
                    'filename' => $filename,
                    'path' => $dir . '/' . $filename
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to upload user image', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                return back()->withErrors(['img' => 'Failed to upload image: ' . $e->getMessage()]);
            }
        } else {
            // If no new image uploaded, don't change the existing img field
            unset($data['img']);
        }

        $user->update($data);

        return back()->with('status', 'User updated successfully' . ($request->hasFile('img') ? ' with new image' : ''));
    }

    public function destroy(User $user): RedirectResponse
    {
        if (!Auth::user()->canDelete()) {
            return redirect()->route('admin.users.index')->with('error', 'You do not have permission to delete users.');
        }

        try {
            \DB::beginTransaction();

            \Log::info("Starting deletion of user {$user->id} ({$user->name})");

            // Step 1: Detach Spatie roles and permissions
            \DB::table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('model_type', 'App\Models\User')
                ->delete();

            \DB::table('model_has_permissions')
                ->where('model_id', $user->id)
                ->where('model_type', 'App\Models\User')
                ->delete();

            \Log::info("Deleted Spatie roles and permissions for user {$user->id}");

            // Step 2: Handle tasks created by this user
            $tasksCreatedByUser = \App\Models\Task::where('created_by', $user->id)->get();
            foreach ($tasksCreatedByUser as $task) {
                $adminUser = User::where('role', 'admin')->first();
                if ($adminUser && $adminUser->id !== $user->id) {
                    $task->created_by = $adminUser->id;
                    $task->save();
                } else {
                    // Find another admin or manager
                    $altAdmin = User::whereIn('role', ['admin', 'manager'])
                        ->where('id', '!=', $user->id)
                        ->first();
                    if ($altAdmin) {
                        $task->created_by = $altAdmin->id;
                        $task->save();
                    } else {
                        $task->forceDelete();
                    }
                }
            }

            // Tasks assigned to this user - set to null (already has onDelete('set null'))
            \App\Models\Task::where('assigned_to', $user->id)->update(['assigned_to' => null]);

            // Tasks with internal approval by this user - set to null
            \App\Models\Task::where('internal_approved_by', $user->id)->update(['internal_approved_by' => null]);

            // Tasks with manager override by this user - set to null
            \App\Models\Task::where('manager_override_by', $user->id)->update(['manager_override_by' => null]);

            // Tasks closed by this user - set to null (already has nullOnDelete)
            \App\Models\Task::where('closed_by', $user->id)->update(['closed_by' => null]);

            \Log::info("Handled task relationships for user {$user->id}");

            // Step 3: Handle projects owned by this user
            $projectsOwnedByUser = \App\Models\Project::where('owner_id', $user->id)->get();
            foreach ($projectsOwnedByUser as $project) {
                $adminUser = User::where('role', 'admin')->first();
                if ($adminUser && $adminUser->id !== $user->id) {
                    $project->owner_id = $adminUser->id;
                    $project->save();
                } else {
                    // Find another admin or manager
                    $altAdmin = User::whereIn('role', ['admin', 'manager'])
                        ->where('id', '!=', $user->id)
                        ->first();
                    if ($altAdmin) {
                        $project->owner_id = $altAdmin->id;
                        $project->save();
                    } else {
                        $project->delete();
                    }
                }
            }

            \Log::info("Handled project ownership for user {$user->id}");

            // Step 4: Detach user from projects (many-to-many relationship)
            $user->projects()->detach();

            // Step 5: Delete employee evaluations where this user was the evaluator
            // (user_id will cascade, but evaluated_by will not)
            \DB::table('employee_evaluations')
                ->where('evaluated_by', $user->id)
                ->delete();

            \Log::info("Deleted employee evaluations by user {$user->id}");

            // Step 6: Handle delete requests
            \DB::table('delete_requests')
                ->where('reviewed_by', $user->id)
                ->update(['reviewed_by' => null]);

            // Step 7: Delete custom notifications
            $user->customNotifications()->delete();
            $user->unifiedNotifications()->delete();

            // Step 8: Delete task histories
            $user->taskHistories()->delete();

            // Step 9: Delete time tracking entries (will cascade)
            // Step 10: Delete user preferences (will cascade)
            // Step 11: Delete performance metrics (will cascade)
            // These will be handled by cascade delete

            \Log::info("Deleted notifications and histories for user {$user->id}");

            // Step 12: Handle task email preparations
            // These have cascadeOnDelete, but let's be explicit
            \DB::table('task_email_preparations')
                ->where('prepared_by', $user->id)
                ->delete();

            // Step 13: Handle task time extension requests
            \DB::table('task_time_extension_requests')
                ->where('reviewed_by', $user->id)
                ->update(['reviewed_by' => null]);

            // Step 14: Handle activity logs (nullable)
            // Already has nullOnDelete, but let's be safe
            \DB::table('activity_logs')
                ->where('user_id', $user->id)
                ->update(['user_id' => null]);

            \Log::info("Cleaned up additional relationships for user {$user->id}");

            // Step 15: Remove stored image if not default
            $old = $user->img;
            if ($old && !in_array($old, ['default.png', 'default.jpg', '1.png', 'default_user.jpg', 'default-user.jpg'])) {
                $oldPath = public_path('uploads/users/'.$old);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // Step 16: Finally, delete the user
            $user->delete();

            \Log::info("Successfully deleted user {$user->id}");

            \DB::commit();

            return redirect()->route('admin.users.index')
                ->with('status', 'User deleted successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error deleting user: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}


