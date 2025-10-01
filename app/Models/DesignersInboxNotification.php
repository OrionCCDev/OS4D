<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesignersInboxNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the email that this notification is about
     */
    public function email()
    {
        return $this->belongsTo(Email::class);
    }

    /**
     * Mark the notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if notification is read
     */
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for notifications of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute()
    {
        return match($this->type) {
            'new_email' => 'bx-envelope',
            'email_reply' => 'bx-reply',
            'email_attachment' => 'bx-paperclip',
            'email_urgent' => 'bx-error',
            default => 'bx-bell'
        };
    }

    /**
     * Get notification color based on type
     */
    public function getColorAttribute()
    {
        return match($this->type) {
            'new_email' => 'primary',
            'email_reply' => 'info',
            'email_attachment' => 'warning',
            'email_urgent' => 'danger',
            default => 'secondary'
        };
    }
}
