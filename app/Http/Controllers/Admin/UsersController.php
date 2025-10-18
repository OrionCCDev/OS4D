<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
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
            $filename = uniqid('u_').'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $data['img'] = $filename;
        }

        $user->update($data);

        return back()->with('status', 'User updated');
    }

    public function destroy(User $user): RedirectResponse
    {
        // remove stored image if not default
        // Never delete default.png, default.jpg, 1.png, default_user.jpg, or default-user.jpg
        $old = $user->img;
        if ($old && !in_array($old, ['default.png', 'default.jpg', '1.png', 'default_user.jpg', 'default-user.jpg'])) {
            $oldPath = public_path('uploads/users/'.$old);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('status', 'User deleted');
    }
}


