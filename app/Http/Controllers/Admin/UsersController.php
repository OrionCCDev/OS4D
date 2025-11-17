<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function index(): View
    {
        $users = User::orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15);
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
            DB::beginTransaction();

            \Log::info("===== Starting deletion of user {$user->id} ({$user->name}, role: {$user->role}) =====");

            // Find replacement admin/manager
            $replacementAdmin = User::whereIn('role', ['admin', 'manager'])
                ->where('id', '!=', $user->id)
                ->first();

            if (!$replacementAdmin && in_array($user->role, ['admin', 'manager'])) {
                DB::rollBack();
                return redirect()->route('admin.users.index')
                    ->with('error', 'Cannot delete the last admin/manager.');
            }

            $userId = $user->id;
            $userName = $user->name;

            // STEP 1: Spatie permissions (polymorphic)
            \Log::info("Step 1: Spatie permissions");
            DB::table('model_has_roles')->where('model_id', $userId)->where('model_type', 'App\\Models\\User')->delete();
            DB::table('model_has_permissions')->where('model_id', $userId)->where('model_type', 'App\\Models\\User')->delete();

            // STEP 2: Task relationships
            \Log::info("Step 2: Task relationships");
            if ($replacementAdmin) {
                DB::table('tasks')->where('created_by', $userId)->update(['created_by' => $replacementAdmin->id]);
            }
            DB::table('tasks')->where('assigned_to', $userId)->update(['assigned_to' => null]);
            DB::table('tasks')->where('internal_approved_by', $userId)->update(['internal_approved_by' => null]);
            DB::table('tasks')->where('manager_override_by', $userId)->update(['manager_override_by' => null]);
            DB::table('tasks')->where('closed_by', $userId)->update(['closed_by' => null]);

            // STEP 3: Task cascading tables
            \Log::info("Step 3: Task cascade tables");
            if (Schema::hasTable('task_assignees')) {
                DB::table('task_assignees')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('task_status_changes')) {
                DB::table('task_status_changes')->where('changed_by', $userId)->delete();
            }
            if (Schema::hasTable('task_approvals')) {
                DB::table('task_approvals')->where('reviewer_id', $userId)->delete();
            }
            if (Schema::hasTable('task_comments')) {
                DB::table('task_comments')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('task_histories')) {
                DB::table('task_histories')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('task_email_preparations')) {
                DB::table('task_email_preparations')->where('prepared_by', $userId)->delete();
            }
            if (Schema::hasTable('task_time_extension_requests')) {
                DB::table('task_time_extension_requests')->where('requested_by', $userId)->delete();
                DB::table('task_time_extension_requests')->where('reviewed_by', $userId)->update(['reviewed_by' => null]);
            }

            // STEP 4: Contractor emails
            \Log::info("Step 4: Contractor emails");
            if (Schema::hasTable('contractor_emails')) {
                DB::table('contractor_emails')->where('sent_by', $userId)->delete();
            }

            // STEP 5: Project relationships
            \Log::info("Step 5: Project relationships");
            if (Schema::hasTable('project_user')) {
                DB::table('project_user')->where('user_id', $userId)->delete();
            }
            if ($replacementAdmin) {
                DB::table('projects')->where('owner_id', $userId)->update(['owner_id' => $replacementAdmin->id]);
            } else {
                DB::table('projects')->where('owner_id', $userId)->delete();
            }

            // STEP 6: Evaluations and performance
            \Log::info("Step 6: Evaluations and performance");
            if (Schema::hasTable('employee_evaluations')) {
                DB::table('employee_evaluations')->where('user_id', $userId)->delete();
                DB::table('employee_evaluations')->where('evaluated_by', $userId)->delete();
            }
            if (Schema::hasTable('performance_metrics')) {
                DB::table('performance_metrics')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('report_templates')) {
                DB::table('report_templates')->where('created_by', $userId)->delete();
            }

            // STEP 7: Notifications
            \Log::info("Step 7: Notifications");
            if (Schema::hasTable('custom_notifications')) {
                DB::table('custom_notifications')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('unified_notifications')) {
                DB::table('unified_notifications')->where('user_id', $userId)->delete();
            }

            // STEP 8: Other tables
            \Log::info("Step 8: Other tables");
            if (Schema::hasTable('delete_requests')) {
                DB::table('delete_requests')->where('requester_id', $userId)->delete();
                DB::table('delete_requests')->where('reviewed_by', $userId)->update(['reviewed_by' => null]);
            }
            if (Schema::hasTable('activity_logs')) {
                DB::table('activity_logs')->where('user_id', $userId)->update(['user_id' => null]);
            }
            if (Schema::hasTable('time_tracking')) {
                DB::table('time_tracking')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('user_preferences')) {
                DB::table('user_preferences')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('project_folder_files')) {
                DB::table('project_folder_files')->where('uploaded_by', $userId)->delete();
            }

            // STEP 9: Remove profile image
            \Log::info("Step 9: Profile image");
            $old = $user->img;
            if ($old && !in_array($old, ['default.png', 'default.jpg', '1.png', 'default_user.jpg', 'default-user.jpg'])) {
                $oldPath = public_path('uploads/users/'.$old);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // STEP 10: Delete user record
            \Log::info("Step 10: Deleting user record");
            DB::table('users')->where('id', $userId)->delete();

            \Log::info("===== Successfully deleted user {$userId} ({$userName}) =====");

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('status', "User '{$userName}' deleted successfully");

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error("===== FAILED to delete user =====");
            \Log::error("Error: {$e->getMessage()}");
            \Log::error("File: {$e->getFile()}:{$e->getLine()}");
            \Log::error("Trace: " . $e->getTraceAsString());

            return redirect()->route('admin.users.index')
                ->with('error', "Failed to delete user: {$e->getMessage()}");
        }
    }

    /**
     * Deactivate a user instead of deleting to preserve historical data
     */
    public function deactivate(User $user): RedirectResponse
    {
        if (!Auth::user()->canDelete()) {
            return redirect()->route('admin.users.index')->with('error', 'You do not have permission to deactivate users.');
        }

        // Prevent deactivating yourself
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot deactivate yourself.');
        }

        // Prevent deactivating the last admin/manager
        if (in_array($user->role, ['admin', 'manager'])) {
            $remainingAdmins = User::whereIn('role', ['admin', 'manager'])
                ->where('status', 'active')
                ->where('id', '!=', $user->id)
                ->count();

            if ($remainingAdmins === 0) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Cannot deactivate the last active admin/manager.');
            }
        }

        try {
            $user->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
                'deactivation_reason' => 'Deactivated by ' . Auth::user()->name,
            ]);

            \Log::info("User {$user->id} ({$user->name}) deactivated by admin " . Auth::id());

            return redirect()->route('admin.users.index')
                ->with('status', "User '{$user->name}' has been deactivated successfully. All historical data is preserved.");
        } catch (\Exception $e) {
            \Log::error("Failed to deactivate user {$user->id}: {$e->getMessage()}");

            return redirect()->route('admin.users.index')
                ->with('error', "Failed to deactivate user: {$e->getMessage()}");
        }
    }

    /**
     * Reactivate a deactivated user
     */
    public function reactivate(User $user): RedirectResponse
    {
        if (!Auth::user()->canDelete()) {
            return redirect()->route('admin.users.index')->with('error', 'You do not have permission to reactivate users.');
        }

        try {
            $user->update([
                'status' => 'active',
                'deactivated_at' => null,
                'deactivation_reason' => null,
            ]);

            \Log::info("User {$user->id} ({$user->name}) reactivated by admin " . Auth::id());

            return redirect()->route('admin.users.index')
                ->with('status', "User '{$user->name}' has been reactivated successfully.");
        } catch (\Exception $e) {
            \Log::error("Failed to reactivate user {$user->id}: {$e->getMessage()}");

            return redirect()->route('admin.users.index')
                ->with('error', "Failed to reactivate user: {$e->getMessage()}");
        }
    }

    /**
     * Force delete a user - bypasses all safety checks
     * WARNING: This permanently deletes the user and all related data
     */
    public function forceDelete(User $user): RedirectResponse
    {
        \Log::info("===== FORCE DELETE METHOD CALLED =====");
        \Log::info("User ID from route: {$user->id}");
        \Log::info("User Name: {$user->name}");
        \Log::info("User Email: {$user->email}");
        \Log::info("Current Auth User ID: " . Auth::id());
        \Log::info("Current Auth User Name: " . (Auth::user() ? Auth::user()->name : 'NULL'));
        \Log::info("Request Method: " . request()->method());
        \Log::info("Request URL: " . request()->fullUrl());
        \Log::info("Request All: " . json_encode(request()->all()));
        \Log::info("CSRF Token Present: " . (request()->has('_token') ? 'YES' : 'NO'));

        if (!Auth::user()->canDelete()) {
            \Log::warning("Force delete blocked: User does not have delete permission");
            return redirect()->route('admin.users.index')->with('error', 'You do not have permission to force delete users.');
        }

        // Prevent force deleting yourself
        if ($user->id === Auth::id()) {
            \Log::warning("Force delete blocked: User trying to delete themselves");
            return redirect()->route('admin.users.index')->with('error', 'You cannot force delete yourself.');
        }

        try {
            DB::beginTransaction();

            \Log::warning("===== FORCE DELETION of user {$user->id} ({$user->name}, role: {$user->role}) by admin " . Auth::id() . " =====");

            $userId = $user->id;
            $userName = $user->name;

            // STEP 1: Spatie permissions (polymorphic)
            \Log::info("Step 1: Spatie permissions");
            DB::table('model_has_roles')->where('model_id', $userId)->where('model_type', 'App\\Models\\User')->delete();
            DB::table('model_has_permissions')->where('model_id', $userId)->where('model_type', 'App\\Models\\User')->delete();

            // STEP 2: Task relationships
            \Log::info("Step 2: Task relationships");
            // Note: created_by has CASCADE DELETE constraint, so we need to reassign or delete tasks
            // Find replacement admin/manager to reassign tasks
            $replacementAdmin = User::whereIn('role', ['admin', 'manager'])
                ->where('id', '!=', $userId)
                ->first();
            
            if ($replacementAdmin) {
                // Reassign tasks to replacement admin/manager
                DB::table('tasks')->where('created_by', $userId)->update(['created_by' => $replacementAdmin->id]);
                \Log::info("Reassigned tasks created by user {$userId} to user {$replacementAdmin->id}");
            } else {
                // No replacement found, tasks will be deleted by CASCADE when user is deleted
                \Log::warning("No replacement admin/manager found. Tasks with created_by = {$userId} will be CASCADE deleted.");
            }
            
            // Update nullable fields
            DB::table('tasks')->where('assigned_to', $userId)->update(['assigned_to' => null]);
            DB::table('tasks')->where('internal_approved_by', $userId)->update(['internal_approved_by' => null]);
            DB::table('tasks')->where('manager_override_by', $userId)->update(['manager_override_by' => null]);
            
            // Update closed_by only if column exists
            if (Schema::hasColumn('tasks', 'closed_by')) {
                DB::table('tasks')->where('closed_by', $userId)->update(['closed_by' => null]);
            }

            // STEP 3: Task cascading tables
            \Log::info("Step 3: Task cascade tables");
            if (Schema::hasTable('task_assignees')) {
                DB::table('task_assignees')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('task_status_changes')) {
                DB::table('task_status_changes')->where('changed_by', $userId)->delete();
            }
            if (Schema::hasTable('task_approvals')) {
                DB::table('task_approvals')->where('reviewer_id', $userId)->delete();
            }
            if (Schema::hasTable('task_comments')) {
                DB::table('task_comments')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('task_histories')) {
                DB::table('task_histories')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('task_email_preparations')) {
                DB::table('task_email_preparations')->where('prepared_by', $userId)->delete();
            }
            if (Schema::hasTable('task_time_extension_requests')) {
                DB::table('task_time_extension_requests')->where('requested_by', $userId)->delete();
                DB::table('task_time_extension_requests')->where('reviewed_by', $userId)->update(['reviewed_by' => null]);
            }

            // STEP 4: Contractor emails
            \Log::info("Step 4: Contractor emails");
            if (Schema::hasTable('contractor_emails')) {
                DB::table('contractor_emails')->where('sent_by', $userId)->delete();
            }

            // STEP 5: Project relationships
            \Log::info("Step 5: Project relationships");
            if (Schema::hasTable('project_user')) {
                DB::table('project_user')->where('user_id', $userId)->delete();
            }
            
            // Note: owner_id has CASCADE DELETE constraint, so we need to reassign or let CASCADE handle it
            if ($replacementAdmin) {
                // Reassign projects to replacement admin/manager
                DB::table('projects')->where('owner_id', $userId)->update(['owner_id' => $replacementAdmin->id]);
                \Log::info("Reassigned projects owned by user {$userId} to user {$replacementAdmin->id}");
            } else {
                // No replacement found, projects will be deleted by CASCADE when user is deleted
                \Log::warning("No replacement admin/manager found. Projects with owner_id = {$userId} will be CASCADE deleted.");
            }

            // STEP 6: Evaluations and performance
            \Log::info("Step 6: Evaluations and performance");
            if (Schema::hasTable('employee_evaluations')) {
                DB::table('employee_evaluations')->where('user_id', $userId)->delete();
                DB::table('employee_evaluations')->where('evaluated_by', $userId)->delete();
            }
            if (Schema::hasTable('performance_metrics')) {
                DB::table('performance_metrics')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('report_templates')) {
                DB::table('report_templates')->where('created_by', $userId)->delete();
            }

            // STEP 7: Notifications
            \Log::info("Step 7: Notifications");
            if (Schema::hasTable('custom_notifications')) {
                DB::table('custom_notifications')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('unified_notifications')) {
                DB::table('unified_notifications')->where('user_id', $userId)->delete();
            }

            // STEP 8: Other tables
            \Log::info("Step 8: Other tables");
            if (Schema::hasTable('delete_requests')) {
                DB::table('delete_requests')->where('requester_id', $userId)->delete();
                DB::table('delete_requests')->where('reviewed_by', $userId)->update(['reviewed_by' => null]);
            }
            if (Schema::hasTable('activity_logs')) {
                DB::table('activity_logs')->where('user_id', $userId)->update(['user_id' => null]);
            }
            if (Schema::hasTable('time_tracking')) {
                DB::table('time_tracking')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('user_preferences')) {
                DB::table('user_preferences')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('project_folder_files')) {
                DB::table('project_folder_files')->where('uploaded_by', $userId)->delete();
            }

            // STEP 9: Remove profile image
            \Log::info("Step 9: Profile image");
            $old = $user->img;
            if ($old && !in_array($old, ['default.png', 'default.jpg', '1.png', 'default_user.jpg', 'default-user.jpg'])) {
                $oldPath = public_path('uploads/users/'.$old);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // STEP 10: Force delete user record
            \Log::info("Step 10: Force deleting user record");
            DB::table('users')->where('id', $userId)->delete();

            \Log::warning("===== Successfully FORCE DELETED user {$userId} ({$userName}) =====");

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('status', "User '{$userName}' has been permanently deleted (force delete).");

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error("===== FAILED to force delete user =====");
            \Log::error("Error: {$e->getMessage()}");
            \Log::error("File: {$e->getFile()}:{$e->getLine()}");
            \Log::error("Trace: " . $e->getTraceAsString());

            return redirect()->route('admin.users.index')
                ->with('error', "Failed to force delete user: {$e->getMessage()}");
        }
    }
}
