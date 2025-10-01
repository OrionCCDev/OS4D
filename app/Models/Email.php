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
        'reply_to_email_id',
        'gmail_message_id',
        'thread_id',
        'email_type',
        'sent_at',
        'delivered_at',
        'opened_at',
        'replied_at',
        'cc_emails',
        'bcc_emails',
        'tracking_pixel_url',
        'is_tracked',
        'user_id',
        'email_source'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'attachments' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'replied_at' => 'datetime',
        'cc_emails' => 'array',
        'bcc_emails' => 'array',
        'is_tracked' => 'boolean',
    ];

    /**
     * Get the task that this email is related to
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who sent this email
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get notifications for this email
     */
    public function notifications()
    {
        return $this->hasMany(EmailNotification::class);
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
