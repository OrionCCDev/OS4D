@php
    $signatureService = app(\App\Services\EmailSignatureService::class);
    $htmlSignature = $signatureService->getSignatureForEmail($user, 'html');
    $plainTextSignature = $signatureService->getSignatureForEmail($user, 'plain');
@endphp

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Email Signature Preview</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshSignaturePreview()" title="Refresh Preview">
                    <i class="bx bx-refresh me-1"></i>Refresh
                </button>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" id="html-preview-btn">HTML</button>
                    <button type="button" class="btn btn-outline-secondary" id="plain-preview-btn">Plain Text</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HTML Signature Preview -->
<div id="html-signature-preview" class="signature-preview">
    <div class="border rounded p-3 bg-light" style="min-height: 200px;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <small class="text-muted">Preview of your email signature as it appears in HTML emails:</small>
            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('html-signature')">
                <i class="bx bx-copy me-1"></i>Copy HTML
            </button>
        </div>
        <div class="signature-content" id="html-signature">
            {!! $htmlSignature !!}
        </div>
    </div>
</div>

<!-- Plain Text Signature Preview -->
<div id="plain-signature-preview" class="signature-preview" style="display: none;">
    <div class="border rounded p-3 bg-light" style="min-height: 200px;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <small class="text-muted">Preview of your email signature as it appears in plain text emails:</small>
            <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('plain-signature')">
                <i class="bx bx-copy me-1"></i>Copy Plain Text
            </button>
        </div>
        <div class="signature-content" id="plain-signature">
            <pre style="white-space: pre-wrap; font-family: monospace; margin: 0;">{{ $plainTextSignature }}</pre>
        </div>
    </div>
</div>

<!-- Signature Information -->
<div class="mt-3">
    <div class="alert alert-info">
        <div class="d-flex align-items-start">
            <i class="bx bx-info-circle me-2 mt-1"></i>
            <div>
                <h6 class="alert-heading mb-1">About Your Email Signature</h6>
                <p class="mb-1">This signature will be automatically added to all emails you send through the system, including:</p>
                <ul class="mb-0 small">
                    <li>Project completion confirmations</li>
                    <li>Task updates and notifications</li>
                    <li>General emails sent via Gmail integration</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.signature-preview .signature-content {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 15px;
    font-family: Arial, sans-serif;
    font-size: 14px;
    line-height: 1.4;
}

.signature-preview .signature-content table {
    margin: 0;
    border-collapse: collapse;
}

.signature-preview .signature-content td {
    padding: 2px 8px 2px 0;
    vertical-align: top;
}

.signature-preview .signature-content img {
    max-width: 150px;
    height: auto;
}

.btn-group .btn.active {
    background-color: #696cff;
    border-color: #696cff;
    color: white;
}

.btn-group .btn:not(.active) {
    background-color: transparent;
    border-color: #d9dee3;
    color: #697a8d;
}

.btn-group .btn:not(.active):hover {
    background-color: #f5f5f9;
    border-color: #696cff;
    color: #696cff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const htmlPreviewBtn = document.getElementById('html-preview-btn');
    const plainPreviewBtn = document.getElementById('plain-preview-btn');
    const htmlPreview = document.getElementById('html-signature-preview');
    const plainPreview = document.getElementById('plain-signature-preview');

    htmlPreviewBtn.addEventListener('click', function() {
        htmlPreviewBtn.classList.add('active');
        htmlPreviewBtn.classList.remove('btn-outline-secondary');
        htmlPreviewBtn.classList.add('btn-outline-primary');

        plainPreviewBtn.classList.remove('active');
        plainPreviewBtn.classList.remove('btn-outline-primary');
        plainPreviewBtn.classList.add('btn-outline-secondary');

        htmlPreview.style.display = 'block';
        plainPreview.style.display = 'none';
    });

    plainPreviewBtn.addEventListener('click', function() {
        plainPreviewBtn.classList.add('active');
        plainPreviewBtn.classList.remove('btn-outline-secondary');
        plainPreviewBtn.classList.add('btn-outline-primary');

        htmlPreviewBtn.classList.remove('active');
        htmlPreviewBtn.classList.remove('btn-outline-primary');
        htmlPreviewBtn.classList.add('btn-outline-secondary');

        plainPreview.style.display = 'block';
        htmlPreview.style.display = 'none';
    });
});

function copyToClipboard(type) {
    const content = document.getElementById(type).innerHTML;
    const textContent = document.getElementById(type).textContent || document.getElementById(type).innerText;

    // Create a temporary textarea element
    const textarea = document.createElement('textarea');
    textarea.value = textContent;
    document.body.appendChild(textarea);
    textarea.select();

    try {
        document.execCommand('copy');
        // Show success message
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bx bx-check me-1"></i>Copied!';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');

        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy to clipboard');
    }

    document.body.removeChild(textarea);
}

// Function to refresh signature preview
function refreshSignaturePreview() {
    const refreshBtn = document.querySelector('button[onclick="refreshSignaturePreview()"]');
    const originalText = refreshBtn.innerHTML;

    // Show loading state
    refreshBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Refreshing...';
    refreshBtn.disabled = true;

    fetch('{{ route("profile.signature-preview") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('html-signature').innerHTML = data.html_signature;
            document.getElementById('plain-signature').innerHTML = '<pre style="white-space: pre-wrap; font-family: monospace; margin: 0;">' + data.plain_text_signature + '</pre>';

            // Show success state briefly
            refreshBtn.innerHTML = '<i class="bx bx-check me-1"></i>Refreshed!';
            refreshBtn.classList.remove('btn-outline-secondary');
            refreshBtn.classList.add('btn-success');

            setTimeout(() => {
                refreshBtn.innerHTML = originalText;
                refreshBtn.classList.remove('btn-success');
                refreshBtn.classList.add('btn-outline-secondary');
                refreshBtn.disabled = false;
            }, 1500);
        })
        .catch(error => {
            console.error('Error refreshing signature preview:', error);

            // Show error state
            refreshBtn.innerHTML = '<i class="bx bx-error me-1"></i>Error';
            refreshBtn.classList.remove('btn-outline-secondary');
            refreshBtn.classList.add('btn-danger');

            setTimeout(() => {
                refreshBtn.innerHTML = originalText;
                refreshBtn.classList.remove('btn-danger');
                refreshBtn.classList.add('btn-outline-secondary');
                refreshBtn.disabled = false;
            }, 2000);
        });
}

// Listen for profile form submission to refresh signature preview
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.querySelector('form[action="{{ route("profile.update") }}"]');
    if (profileForm) {
        profileForm.addEventListener('submit', function() {
            // Refresh signature preview after a short delay to allow the form to submit
            setTimeout(refreshSignaturePreview, 1000);
        });
    }
});
</script>
