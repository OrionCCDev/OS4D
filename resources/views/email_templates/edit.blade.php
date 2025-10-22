@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <h4 class="mb-3">Edit Email Template</h4>
    <div class="card p-3">
        <form method="POST" action="{{ route('email-templates.update', $template) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" class="form-control" value="{{ old('type', $template->type) }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject', $template->subject) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Body</label>
                <textarea name="body" class="form-control" rows="6" required>{{ old('body', $template->body) }}</textarea>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $template->is_active))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="mb-3">
                <label class="form-label">Variables (JSON)</label>
                <textarea name="variables" class="form-control" rows="3">{{ old('variables', json_encode($template->variables)) }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('email-templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection


