<?php

namespace App\Http\Controllers;

use App\Models\ExternalStakeholder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExternalStakeholderController extends Controller
{
    public function index()
    {
        // Only managers can manage external stakeholders
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can manage external stakeholders.');
        }

        $stakeholders = ExternalStakeholder::latest()->paginate(15);
        return view('external-stakeholders.index', compact('stakeholders'));
    }

    public function create()
    {
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can manage external stakeholders.');
        }

        return view('external-stakeholders.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can manage external stakeholders.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:external_stakeholders,email',
            'company' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        ExternalStakeholder::create($validated);

        return redirect()->route('external-stakeholders.index')
            ->with('success', 'External stakeholder created successfully');
    }

    public function edit(ExternalStakeholder $externalStakeholder)
    {
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can manage external stakeholders.');
        }

        return view('external-stakeholders.edit', compact('externalStakeholder'));
    }

    public function update(Request $request, ExternalStakeholder $externalStakeholder)
    {
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can manage external stakeholders.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:external_stakeholders,email,' . $externalStakeholder->id,
            'company' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $externalStakeholder->update($validated);

        return redirect()->route('external-stakeholders.index')
            ->with('success', 'External stakeholder updated successfully');
    }

    public function destroy(ExternalStakeholder $externalStakeholder)
    {
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can manage external stakeholders.');
        }

        if (!Auth::user()->canDelete()) {
            return redirect()->route('external-stakeholders.index')
                ->with('error', 'You do not have permission to delete external stakeholders.');
        }

        $externalStakeholder->delete();

        return redirect()->route('external-stakeholders.index')
            ->with('success', 'External stakeholder deleted successfully');
    }
}

