<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractorController extends Controller
{
    public function index()
    {
        $contractors = Contractor::latest()->paginate(15);
        return view('contractors.index', compact('contractors'));
    }

    public function create()
    {
        return view('contractors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contractors,email',
            'mobile' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'type' => 'required|in:client,consultant,other,orion staff',
        ]);
        Contractor::create($validated);
        return redirect()->route('contractors.index')->with('success', 'Contractor created');
    }

    public function edit(Contractor $contractor)
    {
        return view('contractors.edit', compact('contractor'));
    }

    public function update(Request $request, Contractor $contractor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contractors,email,' . $contractor->id,
            'mobile' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'type' => 'required|in:client,consultant,other,orion staff',
        ]);
        $contractor->update($validated);
        return redirect()->route('contractors.index')->with('success', 'Contractor updated');
    }

    public function destroy(Contractor $contractor)
    {
        if (!Auth::user()->canDelete()) {
            return redirect()->route('contractors.index')->with('error', 'You do not have permission to delete contractors.');
        }

        $contractor->delete();
        return redirect()->route('contractors.index')->with('success', 'Contractor deleted');
    }
}


