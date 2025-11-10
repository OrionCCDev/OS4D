@foreach($requests as $request)
    <div class="dropdown-item d-flex align-items-start">
        <div class="me-3">
            <span class="badge bg-warning text-dark">{{ ucfirst($request->target_type) }}</span>
        </div>
        <div class="flex-grow-1">
            <div class="fw-semibold">{{ $request->target_label ?? 'ID: '.$request->target_id }}</div>
            <small class="text-muted">
                Requested by {{ $request->requester->name ?? 'Unknown' }} &middot; {{ $request->created_at?->diffForHumans() }}
            </small>
        </div>
        <div>
            <a href="{{ route('admin.delete-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                View
            </a>
        </div>
    </div>
@endforeach

@if($requests->isEmpty())
    <div class="dropdown-item text-center text-muted py-3">
        <i class="bx bx-check-circle me-1"></i>No pending requests
    </div>
@else
    <div class="dropdown-divider"></div>
    <a href="{{ route('admin.delete-requests.index', ['status' => 'pending']) }}" class="dropdown-item text-center">
        View all pending requests
    </a>
@endif

