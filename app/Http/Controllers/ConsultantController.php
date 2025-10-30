<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ConsultantController extends Controller
{
    /**
     * Display a listing of consultants.
     */
    public function index()
    {
        $consultants = User::where('role', 'consultant')
            ->latest()
            ->paginate(15);

        return view('consultants.index', compact('consultants'));
    }

    /**
     * Show the form for creating a new consultant.
     */
    public function create()
    {
        return view('consultants.create');
    }

    /**
     * Store a newly created consultant in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
        ]);

        $validated['role'] = 'consultant';
        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';

        User::create($validated);

        return redirect()->route('consultants.index')->with('success', 'Consultant created successfully');
    }

    /**
     * Display the specified consultant.
     */
    public function show(User $consultant)
    {
        if ($consultant->role !== 'consultant') {
            abort(404);
        }

        return view('consultants.show', compact('consultant'));
    }

    /**
     * Show the form for editing the specified consultant.
     */
    public function edit(User $consultant)
    {
        if ($consultant->role !== 'consultant') {
            abort(404);
        }

        return view('consultants.edit', compact('consultant'));
    }

    /**
     * Update the specified consultant in storage.
     */
    public function update(Request $request, User $consultant)
    {
        if ($consultant->role !== 'consultant') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $consultant->id,
            'mobile' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $consultant->update($validated);

        return redirect()->route('consultants.index')->with('success', 'Consultant updated successfully');
    }

    /**
     * Remove the specified consultant from storage.
     */
    public function destroy(User $consultant)
    {
        if ($consultant->role !== 'consultant') {
            abort(404);
        }

        $consultant->delete();

        return redirect()->route('consultants.index')->with('success', 'Consultant deleted successfully');
    }
}
