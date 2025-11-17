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

            // Step 1: Handle tasks created by or assigned to this user
            // For tasks created by this user, reassign to admin or delete
            $tasksCreatedByUser = \App\Models\Task::where('created_by', $user->id)->get();
            foreach ($tasksCreatedByUser as $task) {
                // Option 1: Reassign to an admin user
                $adminUser = User::where('role', 'admin')->first();
                if ($adminUser) {
                    $task->created_by = $adminUser->id;
                    $task->save();
                } else {
                    // Option 2: If no admin exists, force delete the task
                    $task->forceDelete();
                }
            }

            // For tasks assigned to this user, set assigned_to to null
            \App\Models\Task::where('assigned_to', $user->id)->update(['assigned_to' => null]);

            // Step 2: Handle projects owned by this user
            // Reassign projects to admin or delete them
            $projectsOwnedByUser = \App\Models\Project::where('owner_id', $user->id)->get();
            foreach ($projectsOwnedByUser as $project) {
                // Option 1: Reassign to an admin user
                $adminUser = User::where('role', 'admin')->first();
                if ($adminUser) {
                    $project->owner_id = $adminUser->id;
                    $project->save();
                } else {
                    // Option 2: If no admin exists, delete the project (this will cascade delete tasks)
                    $project->delete();
                }
            }

            // Step 3: Detach user from projects (many-to-many relationship)
            $user->projects()->detach();

            // Step 4: Handle other relationships
            // Delete custom notifications
            $user->customNotifications()->delete();
            $user->unifiedNotifications()->delete();

            // Delete task histories
            $user->taskHistories()->delete();

            // Step 5: Remove stored image if not default
            // Never delete default.png, default.jpg, 1.png, default_user.jpg, or default-user.jpg
            $old = $user->img;
            if ($old && !in_array($old, ['default.png', 'default.jpg', '1.png', 'default_user.jpg', 'default-user.jpg'])) {
                $oldPath = public_path('uploads/users/'.$old);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // Step 6: Finally, delete the user
            $user->delete();

            \DB::commit();
            return redirect()->route('admin.users.index')->with('status', 'User deleted successfully');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error deleting user: ' . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}


