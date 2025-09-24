<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_email',
        'to_email',
        'subject',
        'body',
        'received_at',
        'status',
        'task_id',
        'attachments',
        'message_id',
        'reply_to_email_id'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'attachments' => 'array',
    ];

    /**
     * Get the task that this email is related to
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the email this is a reply to
     */
    public function replyTo()
    {
        return $this->belongsTo(Email::class, 'reply_to_email_id');
    }

    /**
     * Get replies to this email
     */
    public function replies()
    {
        return $this->hasMany(Email::class, 'reply_to_email_id');
    }

    /**
     * Scope for unread emails
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope for emails related to a specific task
     */
    public function scopeForTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Get the sender name from email
     */
    public function getSenderNameAttribute()
    {
        $parts = explode('@', $this->from_email);
        return $parts[0];
    }

    /**
     * Get formatted received date
     */
    public function getFormattedReceivedDateAttribute()
    {
        return $this->received_at->format('M d, Y \a\t g:i A');
    }

    /**
     * Get email preview (first 100 characters)
     */
    public function getPreviewAttribute()
    {
        return substr(strip_tags($this->body), 0, 100) . '...';
    }
}
