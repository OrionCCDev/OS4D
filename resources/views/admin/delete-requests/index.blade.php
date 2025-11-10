@extends('layouts.app')

@section('content')
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0">Delete Requests</h4>
            <small class="text-muted">Review and approve or reject deletion requests submitted by sub-admins.</small>
        </div>
        <div>
            <form class="d-inline" method="GET" action="{{ route('admin.delete-requests.index') }}">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </form>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Item</th>
                    <th>Requested By</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Requested At</th>
                    <th>Reviewed By</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>#{{ $request->id }}</td>
                        <td>
                            <div class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $request->target_type) }}</div>
                            <small class="text-muted">{{ $request->target_label ?? 'ID: '.$request->target_id }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $request->requester->name ?? 'Unknown' }}</div>
                            <small class="text-muted">{{ $request->requester->email ?? '' }}</small>
                        </td>
                        <td>{{ $request->reason ? \Illuminate\Support\Str::limit($request->reason, 80) : '—' }}</td>
                        <td>
                            <span class="badge {{ $request->status === 'pending' ? 'bg-warning' : ($request->status === 'approved' ? 'bg-success' : 'bg-danger') }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $request->reviewer?->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.delete-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show-alt me-1"></i>View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bx bx-info-circle me-1"></i>No delete requests found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection

