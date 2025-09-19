@extends('layouts.app')

@section('content')
<div class="container-xxl container-p-y">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Edit User</h4>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
  </div>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user])
        <div class="mt-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary">Update</button>
          <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection


