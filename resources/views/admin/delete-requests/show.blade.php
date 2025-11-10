@extends('layouts.app')

@section('content')
<div class="container container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0">Delete Request #{{ $deleteRequest->id }}</h4>
            <small class="text-muted">
                Requested by {{ $deleteRequest->requester->name ?? 'Unknown user' }} &middot;
                {{ $deleteRequest->created_at?->diffForHumans() }}
            </small>
        </div>
        <a href="{{ route('admin.delete-requests.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i>Back
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Request Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Item Type</dt>
                        <dd class="col-sm-8 text-capitalize">{{ str_replace('_', ' ', $deleteRequest->target_type) }}</dd>

                        <dt class="col-sm-4">Item Label</dt>
                        <dd class="col-sm-8">{{ $deleteRequest->target_label ?? 'ID: ' . $deleteRequest->target_id }}</dd>

                        <dt class="col-sm-4">Requested By</dt>
                        <dd class="col-sm-8">
                            {{ $deleteRequest->requester->name ?? 'Unknown user' }}<br>
                            <small class="text-muted">{{ $deleteRequest->requester->email ?? '' }}</small>
                        </dd>

                        <dt class="col-sm-4">Reason</dt>
                        <dd class="col-sm-8">{{ $deleteRequest->reason ?: '—' }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $deleteRequest->status === 'pending' ? 'bg-warning' : ($deleteRequest->status === 'approved' ? 'bg-success' : 'bg-danger') }}">
                                {{ ucfirst($deleteRequest->status) }}
                            </span>
                        </dd>

                        @if($deleteRequest->reviewed_at)
                            <dt class="col-sm-4">Reviewed At</dt>
                            <dd class="col-sm-8">{{ $deleteRequest->reviewed_at->format('Y-m-d H:i') }}</dd>
                        @endif

                        @if($deleteRequest->reviewer)
                            <dt class="col-sm-4">Reviewed By</dt>
                            <dd class="col-sm-8">{{ $deleteRequest->reviewer->name }}</dd>
                        @endif

                        @if($deleteRequest->review_notes)
                            <dt class="col-sm-4">Review Notes</dt>
                            <dd class="col-sm-8">{{ $deleteRequest->review_notes }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Impact Summary</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $effectSummary }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Target Snapshot</h5>
                </div>
                <div class="card-body">
                    @if($target)
                        <pre class="bg-light p-3 rounded small mb-0">{{ json_encode($target instanceof \Illuminate\Database\Eloquent\Model ? $target->only(['id', 'name', 'title', 'email']) : $target, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bx bx-error me-1"></i>The target item no longer exists.
                        </div>
                    @endif
                </div>
            </div>

            @if($deleteRequest->isPending())
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Review Actions</h5>
                    </div>
                    <div class="card-body">
                        <form id="delete-request-approve-form"
                              action="{{ route('admin.delete-requests.approve', $deleteRequest) }}"
                              method="POST"
                              class="mb-2"
                              data-effect-summary="{{ $effectSummary }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Approval Notes (optional)</label>
                                <textarea name="review_notes" class="form-control" rows="2" placeholder="Add any notes (optional)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bx bx-check-circle me-1"></i>Approve & Delete
                            </button>
                        </form>

                        <form action="{{ route('admin.delete-requests.reject', $deleteRequest) }}" method="POST" onsubmit="return confirm('Reject this delete request?');">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Rejection Notes (optional)</label>
                                <textarea name="review_notes" class="form-control" rows="2" placeholder="Explain reason for rejection (optional)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bx bx-x-circle me-1"></i>Reject Request
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bx bx-info-circle text-muted" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2 text-muted">This request has already been {{ $deleteRequest->status }}.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const approveForm = document.getElementById('delete-request-approve-form');
        if (approveForm) {
            approveForm.addEventListener('submit', function (event) {
                const summary = this.getAttribute('data-effect-summary') || 'This will delete the selected item.';
                const confirmed = confirm('Approve this delete request?\n\n' + summary);
                if (!confirmed) {
                    event.preventDefault();
                }
            });
        }
    });
</script>
@endpush
@endsection

