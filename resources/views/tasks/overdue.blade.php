@extends('layouts.app')

@section('content')
<div class="container flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-0">
                <i class="bx bx-error-circle text-danger me-2"></i>
                {{ $isManagerView ? 'Team Overdue Tasks' : 'My Overdue Tasks' }}
            </h4>
            <small class="text-muted">
                Showing tasks where the due date has passed and no confirmation email was sent yet.
            </small>
        </div>
        <div class="text-end">
            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                <i class="bx bx-timer me-1"></i>
                Total overdue: {{ $tasks->total() }}
            </span>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('overdue-tasks.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">
                        <i class="bx bx-search me-1"></i>Search Tasks
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Search by task name, reference, or code"
                           value="{{ $filters['search'] ?? '' }}">
                </div>

                <div class="col-md-4">
                    <label for="project" class="form-label">
                        <i class="bx bx-briefcase me-1"></i>Project
                    </label>
                    <input type="text"
                           class="form-control"
                           id="project"
                           name="project"
                           placeholder="Filter by project name or code"
                           value="{{ $filters['project'] ?? '' }}">
                </div>

                @if($isManagerView)
                    <div class="col-md-4">
                        <label for="assigned_to" class="form-label">
                            <i class="bx bx-user me-1"></i>Assigned User
                        </label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">All users</option>
                            @foreach($users as $userOption)
                                <option value="{{ $userOption->id }}" @selected(($filters['assigned_to'] ?? null) == $userOption->id)>
                                    {{ $userOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-filter-alt me-1"></i>Apply filters
                    </button>
                    <a href="{{ route('overdue-tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-reset me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 22%">Task</th>
                        <th style="width: 18%">Assigned To</th>
                        <th style="width: 20%">Project</th>
                        <th style="width: 14%">Due Date</th>
                        <th style="width: 14%">Overdue For</th>
                        <th style="width: 12%" class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($tasks as $task)
                        @php
                            $dueDate = $task->due_date ? $task->due_date->copy() : null;
                            $overdueText = 'N/A';
                            if ($dueDate) {
                                $diff = $dueDate->diff(now());
                                $parts = [];
                                if ($diff->d > 0) {
                                    $parts[] = $diff->d . 'd';
                                }
                                if ($diff->h > 0) {
                                    $parts[] = $diff->h . 'h';
                                }
                                if ($diff->d === 0 && $diff->h === 0) {
                                    $parts[] = $diff->i > 0 ? $diff->i . 'm' : '<1m';
                                }
                                $overdueText = implode(' ', $parts);
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $task->title }}</div>
                                <small class="text-muted text-uppercase">{{ str_replace('_', ' ', $task->status) }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ optional($task->assignee)->name ?? 'Unassigned' }}</div>
                                <small class="text-muted">{{ optional($task->assignee)->email }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ optional($task->project)->name ?? 'No project' }}</div>
                                @if(optional($task->project)->short_code)
                                    <small class="text-muted">{{ $task->project->short_code }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold {{ $dueDate ? 'text-danger' : 'text-muted' }}">
                                    {{ $dueDate ? $dueDate->format('M d, Y') : 'Not set' }}
                                </div>
                                @if($dueDate)
                                    <small class="text-muted">{{ $dueDate->diffForHumans(now(), ['parts' => 1, 'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">{{ $overdueText }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show-alt me-1"></i>View
                                    </a>
                                    @if($isManagerView)
                                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bx bx-edit-alt me-1"></i>Edit
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger js-send-overdue-reminder"
                                                data-task-id="{{ $task->id }}">
                                            <i class="bx bx-mail-send me-1"></i>Send Reminder
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bx bx-smile text-success fs-1 d-block mb-2"></i>
                                <p class="mb-0 fw-semibold">No overdue tasks 🎉</p>
                                <small class="text-muted">All tasks are up to date.</small>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($tasks->hasPages())
            <div class="card-footer">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
</div>

@if($isManagerView)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-send-overdue-reminder').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const taskId = this.getAttribute('data-task-id');
                    if (!taskId) {
                        return;
                    }

                    let note = prompt('Optional message to include in the overdue reminder (leave blank for default message):');
                    if (note === null) {
                        return; // user cancelled
                    }

                    this.disabled = true;
                    this.classList.add('disabled');

                    window.sendOverdueReminder(taskId, note)
                        .finally(() => {
                            this.disabled = false;
                            this.classList.remove('disabled');
                        });
                });
            });
        });
    </script>
@endif
@endsection

