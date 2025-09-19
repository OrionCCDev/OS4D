<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::latest()->paginate(15);
        return view('email_templates.index', compact('templates'));
    }

    public function create()
    {
        return view('email_templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
            'variables' => 'nullable|array',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        EmailTemplate::create($validated);
        return redirect()->route('email-templates.index')->with('success', 'Template created');
    }

    public function edit(EmailTemplate $email_template)
    {
        return view('email_templates.edit', ['template' => $email_template]);
    }

    public function update(Request $request, EmailTemplate $email_template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
            'variables' => 'nullable|array',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $email_template->update($validated);
        return redirect()->route('email-templates.index')->with('success', 'Template updated');
    }

    public function destroy(EmailTemplate $email_template)
    {
        $email_template->delete();
        return redirect()->route('email-templates.index')->with('success', 'Template deleted');
    }
}


