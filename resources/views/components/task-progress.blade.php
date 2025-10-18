@props(['task', 'showLabel' => true, 'showPercentage' => true, 'size' => 'md'])

@php
    $progress = $task->progress_percentage ?? 0;
    $status = $task->progress_status ?? 'Unknown';
    $stage = $task->progress_stage ?? 'pending';
    $color = $task->progress_color ?? 'secondary';

    // Size classes
    $sizeClasses = [
        'sm' => 'height: 4px; font-size: 10px;',
        'md' => 'height: 8px; font-size: 12px;',
        'lg' => 'height: 12px; font-size: 14px;',
    ];

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div class="task-progress" {{ $attributes->merge(['class' => 'task-progress-container']) }}>
    @if($showLabel)
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-semibold text-{{ $color }}" style="font-size: 12px;">
                {{ $status }}
            </span>
            @if($showPercentage)
                <span class="text-muted" style="font-size: 11px;">
                    {{ $progress }}%
                </span>
            @endif
        </div>
    @endif

    <div class="progress" style="{{ $sizeClass }}">
        <div class="progress-bar bg-{{ $color }}"
             role="progressbar"
             style="width: {{ $progress }}%; transition: width 0.3s ease;"
             aria-valuenow="{{ $progress }}"
             aria-valuemin="0"
             aria-valuemax="100">
        </div>
    </div>

    @if($stage === 'client_review')
        <div class="mt-1">
            <small class="text-warning">
                <i class="bx bx-time me-1"></i>Waiting for client/consultant feedback
            </small>
        </div>
    @elseif($stage === 'user_work' && $progress < 85)
        <div class="mt-1">
            <small class="text-primary">
                <i class="bx bx-user me-1"></i>User work in progress
            </small>
        </div>
    @elseif($stage === 'completed')
        <div class="mt-1">
            <small class="text-success">
                <i class="bx bx-check-circle me-1"></i>Task completed
            </small>
        </div>
    @endif
</div>
