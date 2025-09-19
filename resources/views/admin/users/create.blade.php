@extends('layouts.app')

@section('content')
<div class="container-xxl container-p-y">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Add User</h4>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.users._form')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection


