@props([
    'title' => 'Page Title',
    'subtitle' => null,
    'icon' => 'bx-home',
    'theme' => 'default',
    'breadcrumbs' => []
])

@php
    $gradients = [
        'default' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'emails' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'tasks' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'projects' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        'notifications' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'dashboard' => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
    ];

    $gradient = $gradients[$theme] ?? $gradients['default'];
@endphp

<!-- Modern Breadcrumb Component -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4" style="background: {{ $gradient }}; color: white;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="{{ $icon }} fs-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="mb-1 fw-bold text-white">{{ $title }}</h3>
                        @if($subtitle)
                            <p class="mb-0 text-white-50">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @if(count($breadcrumbs) > 0)
            <div class="col-md-4">
                <div class="text-md-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end" style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 8px 16px;">
                            @foreach($breadcrumbs as $index => $crumb)
                                @if($loop->last)
                                    <li class="breadcrumb-item active text-white" aria-current="page">
                                        @if(isset($crumb['icon']))
                                            <i class="{{ $crumb['icon'] }} me-1"></i>
                                        @endif
                                        {{ $crumb['title'] }}
                                    </li>
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ $crumb['url'] }}" class="text-white-50 text-decoration-none">
                                            @if(isset($crumb['icon']))
                                                <i class="{{ $crumb['icon'] }} me-1"></i>
                                            @endif
                                            {{ $crumb['title'] }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    </nav>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<style>
    /* In your modern-breadcrumb component CSS */
.modern-breadcrumb {
    position: relative !important;
    display: block !important;
    width: 100% !important;
    z-index: 1 !important;
}
</style>
