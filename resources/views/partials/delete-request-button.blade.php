@php
    $buttonClass = $class ?? 'btn btn-sm btn-outline-danger';
    $buttonText = $text ?? 'Request Delete';
    $buttonIcon = $icon ?? 'bx bx-trash';
    $redirectUrl = $redirect ?? url()->full();
@endphp

@php
    $extraAttributes = $attributes ?? '';
@endphp

<button type="button"
        class="{{ $buttonClass }} delete-request-trigger"
        data-bs-toggle="modal"
        data-bs-target="#deleteRequestModal"
        data-target-type="{{ $type }}"
        data-target-id="{{ $id }}"
        data-target-label="{{ $label }}"
        data-redirect="{{ $redirectUrl }}"
        {!! $extraAttributes !!}>
    <i class="{{ $buttonIcon }}"></i> {{ $buttonText }}
</button>

