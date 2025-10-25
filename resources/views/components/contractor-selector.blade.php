@props([
    'contractors' => collect(),
    'selectedContractors' => collect(),
    'name' => 'contractors',
    'label' => 'Select Contractors',
    'showTypeFilter' => true,
    'multiple' => true
])

<div class="contractor-selector">
    <label class="form-label">{{ $label }}</label>

    @if($showTypeFilter)
    <div class="mb-3">
        <div class="btn-group" role="group" aria-label="Contractor type filter">
            <input type="radio" class="btn-check" name="contractor_type_filter" id="filter_all" value="all" checked>
            <label class="btn btn-outline-primary btn-sm" for="filter_all">All</label>

            <input type="radio" class="btn-check" name="contractor_type_filter" id="filter_orion_staff" value="orion staff">
            <label class="btn btn-outline-primary btn-sm" for="filter_orion_staff">Orion Staff</label>

            <input type="radio" class="btn-check" name="contractor_type_filter" id="filter_client" value="client">
            <label class="btn btn-outline-primary btn-sm" for="filter_client">Clients</label>

            <input type="radio" class="btn-check" name="contractor_type_filter" id="filter_consultant" value="consultant">
            <label class="btn btn-outline-primary btn-sm" for="filter_consultant">Consultants</label>

            <input type="radio" class="btn-check" name="contractor_type_filter" id="filter_other" value="other">
            <label class="btn btn-outline-primary btn-sm" for="filter_other">Other</label>
        </div>
    </div>
    @endif

    <div class="contractor-search mb-3">
        <input type="text"
               class="form-control"
               id="contractor_search"
               placeholder="Search contractors by name, email, or company..."
               autocomplete="off">
    </div>

    <div class="contractor-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 0.5rem;">
        @foreach($contractors as $contractor)
            <div class="contractor-item"
                 data-contractor-id="{{ $contractor->id }}"
                 data-contractor-type="{{ $contractor->type }}"
                 data-contractor-name="{{ strtolower($contractor->name) }}"
                 data-contractor-email="{{ strtolower($contractor->email) }}"
                 data-contractor-company="{{ strtolower($contractor->company_name ?? '') }}">

                <div class="form-check">
                    <input class="form-check-input contractor-checkbox"
                           type="checkbox"
                           name="{{ $name }}[]"
                           value="{{ $contractor->id }}"
                           id="contractor_{{ $contractor->id }}"
                           {{ $selectedContractors->contains($contractor->id) ? 'checked' : '' }}>

                    <label class="form-check-label w-100" for="contractor_{{ $contractor->id }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ $contractor->name }}</div>
                                <div class="text-muted small">
                                    {{ $contractor->email }}
                                    @if($contractor->company_name)
                                        • {{ $contractor->company_name }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $contractor->type === 'orion staff' ? 'primary' : ($contractor->type === 'client' ? 'success' : ($contractor->type === 'other' ? 'secondary' : 'info')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $contractor->type)) }}
                                </span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        @endforeach

        @if($contractors->isEmpty())
            <div class="text-center text-muted py-3">
                <i class="fas fa-users fa-2x mb-2"></i>
                <p>No contractors available</p>
            </div>
        @endif
    </div>

    <div class="selected-contractors mt-3" id="selected_contractors_summary" style="display: none;">
        <h6 class="text-muted">Selected Contractors:</h6>
        <div id="selected_contractors_list" class="d-flex flex-wrap gap-1"></div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const contractorItems = document.querySelectorAll('.contractor-item');
    const searchInput = document.getElementById('contractor_search');
    const typeFilters = document.querySelectorAll('input[name="contractor_type_filter"]');
    const selectedSummary = document.getElementById('selected_contractors_summary');
    const selectedList = document.getElementById('selected_contractors_list');
    const checkboxes = document.querySelectorAll('.contractor-checkbox');

    // Current filter state
    let currentSearchTerm = '';
    let currentTypeFilter = 'all';

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearchTerm = this.value.toLowerCase();
            applyFilters();
        });
    }

    // Type filter functionality
    typeFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            currentTypeFilter = this.value;
            applyFilters();
        });
    });

    // Apply both search and type filters together
    function applyFilters() {
        contractorItems.forEach(item => {
            const name = item.dataset.contractorName;
            const email = item.dataset.contractorEmail;
            const company = item.dataset.contractorCompany;
            const type = item.dataset.contractorType;

            // Check search term match
            const searchMatches = currentSearchTerm === '' ||
                                name.includes(currentSearchTerm) ||
                                email.includes(currentSearchTerm) ||
                                company.includes(currentSearchTerm);

            // Check type filter match
            const typeMatches = currentTypeFilter === 'all' || type === currentTypeFilter;

            // Show item only if both filters match
            const shouldShow = searchMatches && typeMatches;
            item.style.display = shouldShow ? 'block' : 'none';
        });
    }

    // Update selected contractors summary
    function updateSelectedSummary() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);

        if (selected.length > 0) {
            selectedSummary.style.display = 'block';
            selectedList.innerHTML = selected.map(cb => {
                const item = cb.closest('.contractor-item');
                const name = item.querySelector('.fw-medium').textContent;
                const type = item.querySelector('.badge').textContent;
                return `<span class="badge bg-secondary me-1 mb-1">${name} (${type})</span>`;
            }).join('');
        } else {
            selectedSummary.style.display = 'none';
        }
    }

    // Update summary when checkboxes change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedSummary);
    });

    // Initial summary update
    updateSelectedSummary();
});
</script>
@endpush

@push('styles')
<style>
.contractor-item {
    padding: 0.5rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.2s;
}

.contractor-item:hover {
    background-color: #f8f9fa;
}

.contractor-item:last-child {
    border-bottom: none;
}

.form-check-input:checked + .form-check-label {
    color: #0d6efd;
}

.contractor-list::-webkit-scrollbar {
    width: 6px;
}

.contractor-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.contractor-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.contractor-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.btn-check:checked + .btn-outline-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}
</style>
@endpush
