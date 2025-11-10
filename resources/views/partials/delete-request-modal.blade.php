<div class="modal fade" id="deleteRequestModal" tabindex="-1" aria-labelledby="deleteRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('delete-requests.store') }}">
                @csrf
                <input type="hidden" name="target_type" id="delete-request-target-type">
                <input type="hidden" name="target_id" id="delete-request-target-id">
                <input type="hidden" name="target_label" id="delete-request-target-label">
                <input type="hidden" name="redirect_url" id="delete-request-redirect-url">

                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRequestModalLabel">
                        <i class="bx bx-trash me-2"></i>Request Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        This item cannot be deleted directly. Submit a request and an administrator will review it.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" id="delete-request-display-label" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="delete-request-reason" class="form-label">Reason (optional)</label>
                        <textarea class="form-control" id="delete-request-reason" name="reason" rows="3"
                                  placeholder="Provide any details that will help the administrator understand why this item should be deleted."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-send me-1"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('deleteRequestModal');
        if (!modalElement) {
            return;
        }

        modalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) {
                return;
            }

            const targetType = button.getAttribute('data-target-type');
            const targetId = button.getAttribute('data-target-id');
            const targetLabel = button.getAttribute('data-target-label');
            const redirectUrl = button.getAttribute('data-redirect') || window.location.href;

            modalElement.querySelector('#delete-request-target-type').value = targetType || '';
            modalElement.querySelector('#delete-request-target-id').value = targetId || '';
            modalElement.querySelector('#delete-request-target-label').value = targetLabel || '';
            modalElement.querySelector('#delete-request-display-label').value = targetLabel || 'Selected item';
            modalElement.querySelector('#delete-request-redirect-url').value = redirectUrl;
            modalElement.querySelector('#delete-request-reason').value = '';
        });
    });
</script>
@endpush

