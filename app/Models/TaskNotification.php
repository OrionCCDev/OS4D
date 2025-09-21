<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'external_stakeholder_id',
        'notification_type',
        'sent_at',
        'status',
        'email_subject',
        'email_content',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function externalStakeholder()
    {
        return $this->belongsTo(ExternalStakeholder::class);
    }
}

