<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalStakeholder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'company',
        'role',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function taskNotifications()
    {
        return $this->hasMany(TaskNotification::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

