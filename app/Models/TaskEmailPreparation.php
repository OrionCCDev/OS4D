<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskEmailPreparation extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'prepared_by',
        'to_emails',
        'cc_emails',
        'bcc_emails',
        'subject',
        'body',
        'attachments',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'sent_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function getToEmailsArrayAttribute()
    {
        return $this->to_emails ? explode(',', $this->to_emails) : [];
    }

    public function getCcEmailsArrayAttribute()
    {
        return $this->cc_emails ? explode(',', $this->cc_emails) : [];
    }

    public function getBccEmailsArrayAttribute()
    {
        return $this->bcc_emails ? explode(',', $this->bcc_emails) : [];
    }
}
