<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnifiedNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'type',
        'title',
        'message',
        'data',
        'task_id',
        'email_id',
        'project_id',
        'is_read',
        'read_at',
        'priority',
        'status',
        'action_url',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $appends = [
        'requires_action',
        'color',
        'icon',
        'badge_color',
        'time_ago',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function email()
    {
        return $this->belongsTo(Email::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTaskNotifications($query)
    {
        return $query->where('category', 'task');
    }

    public function scopeEmailNotifications($query)
    {
        return $query->where('category', 'email');
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    public function delete()
    {
        $this->update(['status' => 'deleted']);
    }

    // Accessors
    public function getIconAttribute()
    {
        return match($this->category) {
            'task' => match($this->type) {
                'task_assigned' => 'bx-task',
                'task_completed' => 'bx-check-circle',
                'task_overdue' => 'bx-error-circle',
                'task_updated' => 'bx-edit',
                'task_comment' => 'bx-message',
                default => 'bx-task'
            },
            'email' => match($this->type) {
                'email_reply' => 'bx-reply',
                'email_received' => 'bx-envelope',
                'email_sent' => 'bx-send',
                'email_attachment' => 'bx-paperclip',
                'email_urgent' => 'bx-error',
                default => 'bx-envelope'
            },
            default => 'bx-bell'
        };
    }

    public function getColorAttribute()
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'normal' => 'primary',
            'low' => 'secondary',
            default => 'primary'
        };
    }

    public function getBadgeColorAttribute()
    {
        return match($this->category) {
            'task' => 'success',
            'email' => 'info',
            default => 'primary'
        };
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Static methods for creating notifications
    public static function createTaskNotification($userId, $type, $title, $message, $data = [], $taskId = null, $priority = 'normal')
    {
        return static::create([
            'user_id' => $userId,
            'category' => 'task',
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'task_id' => $taskId,
            'priority' => $priority,
            'status' => 'active',
        ]);
    }

    public static function createEmailNotification($userId, $type, $title, $message, $data = [], $emailId = null, $priority = 'normal')
    {
        return static::create([
            'user_id' => $userId,
            'category' => 'email',
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'email_id' => $emailId,
            'priority' => $priority,
            'status' => 'active',
        ]);
    }

    // Bulk operations
    public static function markAllAsReadForUser($userId, $category = null)
    {
        $query = static::where('user_id', $userId)->where('is_read', false);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public static function getUnreadCountForUser($userId, $category = null)
    {
        $query = static::where('user_id', $userId)->where('is_read', false)->where('status', 'active');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->count();
    }

    /**
     * Determine if this notification requires action from the user
     */
    public function requiresAction()
    {
        // Define notification types that require action
        $actionableTypes = [
            // User actionable tasks
            'task_assigned',
            'task_resubmit_required',
            'task_resubmit_enhanced',
            'task_overdue',

            // Manager actionable tasks
            'task_submitted_for_review',
            'task_waiting_for_review',

            // Email notifications that may require action
            'email_reply',
            'email_received'
        ];

        // Check if type requires action OR priority is high/urgent
        return in_array($this->type, $actionableTypes) ||
               in_array($this->priority, ['high', 'urgent']);
    }

    /**
     * Get if notification requires action (accessor)
     */
    public function getRequiresActionAttribute()
    {
        return $this->requiresAction();
    }

    /**
     * Get notification color for display
     */
    public function getColorAttribute()
    {
        $colorMap = [
            'task_assigned' => 'primary',
            'task_completed' => 'success',
            'task_overdue' => 'danger',
            'task_status_changed' => 'info',
            'task_resubmit_required' => 'warning',
            'task_resubmit_enhanced' => 'warning',
            'task_submitted_for_review' => 'info',
            'task_waiting_for_review' => 'info',
            'email_received' => 'info',
            'engineering_inbox_received' => 'purple',
            'test_notification' => 'secondary',
        ];

        return $colorMap[$this->type] ?? 'secondary';
    }

    /**
     * Get notification icon for display
     */
    public function getIconAttribute()
    {
        $iconMap = [
            'task_assigned' => 'bx-task',
            'task_completed' => 'bx-check-circle',
            'task_overdue' => 'bx-error-circle',
            'task_status_changed' => 'bx-edit',
            'task_resubmit_required' => 'bx-refresh',
            'task_resubmit_enhanced' => 'bx-refresh',
            'task_submitted_for_review' => 'bx-send',
            'task_waiting_for_review' => 'bx-time',
            'email_received' => 'bx-envelope',
            'engineering_inbox_received' => 'bx-inbox',
            'test_notification' => 'bx-bell',
        ];

        return $iconMap[$this->type] ?? 'bx-bell';
    }

    /**
     * Get notification badge color for display
     */
    public function getBadgeColorAttribute()
    {
        $badgeColorMap = [
            'task' => 'primary',
            'email' => 'info',
        ];

        return $badgeColorMap[$this->category] ?? 'secondary';
    }

    /**
     * Get time ago for display
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
