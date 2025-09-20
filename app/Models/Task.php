<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'folder_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'assigned_at',
        'started_at',
        'completed_at',
        'completion_notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function folder()
    {
        return $this->belongsTo(ProjectFolder::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function customNotifications()
    {
        return $this->hasMany(CustomNotification::class, 'data->task_id');
    }

    // Status management methods
    public function assignTo(User $user)
    {
        $this->update([
            'assigned_to' => $user->id,
            'assigned_at' => now(),
            'status' => 'assigned'
        ]);

        // Create history record
        $this->histories()->create([
            'user_id' => auth()->id() ?? 1, // Fallback for testing
            'action' => 'assigned',
            'new_value' => $user->name,
            'description' => "Task assigned to {$user->name}",
            'metadata' => ['assigned_to' => $user->id]
        ]);

        // Send notification to assigned user
        $this->sendNotification($user, 'task_assigned', 'Task Assigned', "You have been assigned a new task: {$this->title}");

        // Optionally notify managers about the assignment
        $this->notifyManagers('task_assigned', 'Task Assigned', "Task '{$this->title}' assigned to {$user->name}");
    }

    public function changeStatus(string $status, string $notes = null)
    {
        $oldStatus = $this->status;

        $updateData = ['status' => $status];

        if ($status === 'in_progress' && !$this->started_at) {
            $updateData['started_at'] = now();
        }

        if (in_array($status, ['completed', 'approved']) && !$this->completed_at) {
            $updateData['completed_at'] = now();
        }

        if ($notes) {
            $updateData['completion_notes'] = $notes;
        }

        $this->update($updateData);

        // Create history record
        $this->histories()->create([
            'user_id' => auth()->id() ?? 1, // Fallback for testing
            'action' => 'status_changed',
            'old_value' => $oldStatus,
            'new_value' => $status,
            'description' => "Status changed from {$oldStatus} to {$status}" . ($notes ? ": {$notes}" : ''),
            'metadata' => ['notes' => $notes]
        ]);

        // Send notification to managers
        $this->notifyManagers('task_status_changed', 'Task Status Changed', "Task '{$this->title}' status changed to {$status}");
    }

    private function sendNotification(User $user, string $type, string $title, string $message)
    {
        CustomNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => [
                'task_id' => $this->id,
                'project_id' => $this->project_id,
                'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null
            ]
        ]);
    }

    private function notifyManagers(string $type, string $title, string $message)
    {
        $managers = User::where('role', 'admin')->orWhere('role', 'manager')->get();

        foreach ($managers as $manager) {
            $this->sendNotification($manager, $type, $title, $message);
        }
    }

    // Task statistics methods
    public function getCompletionTimeAttribute()
    {
        if ($this->status === 'completed' && $this->completed_at) {
            $startDate = $this->assigned_at ?? $this->created_at;
            return $startDate ? $startDate->diffInDays($this->completed_at) : null;
        }
        return null;
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->status !== 'completed' && $this->due_date) {
            $now = now();
            if ($this->due_date && $this->due_date->isFuture()) {
                return ceil($this->due_date->diffInDays($now));
            } else {
                return $this->due_date ? -ceil($this->due_date->diffInDays($now)) : null; // Negative for overdue
            }
        }
        return null;
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-secondary',
            'assigned' => 'bg-info',
            'in_progress' => 'bg-primary',
            'in_review' => 'bg-warning',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'completed' => 'bg-success',
            default => 'bg-secondary'
        };
    }

    public function getPriorityBadgeClassAttribute()
    {
        return match($this->priority) {
            'low' => 'bg-success',
            'normal' => 'bg-primary',
            'medium' => 'bg-info',
            'high' => 'bg-warning',
            'urgent' => 'bg-danger',
            'critical' => 'bg-dark',
            default => 'bg-primary'
        };
    }
}


