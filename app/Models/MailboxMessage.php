<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailboxMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'from_email',
        'to_email',
        'subject',
        'body',
        'headers',
        'attachments',
        'received_at',
        'processed',
    ];

    protected $casts = [
        'headers' => 'array',
        'attachments' => 'array',
        'received_at' => 'datetime',
        'processed' => 'boolean',
    ];

    /**
     * Scope for unprocessed messages
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope for recent messages
     */
    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('received_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Mark message as processed
     */
    public function markAsProcessed()
    {
        $this->update(['processed' => true]);
    }
}
