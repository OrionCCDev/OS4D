<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'img',
        'notification_sound_enabled',
        'gmail_token',
        'gmail_refresh_token',
        'gmail_connected',
        'gmail_connected_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_sound_enabled' => 'boolean',
            'gmail_connected' => 'boolean',
            'gmail_connected_at' => 'datetime',
        ];
    }

    // Task relationships
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function taskHistories()
    {
        return $this->hasMany(TaskHistory::class);
    }

    public function customNotifications()
    {
        return $this->hasMany(CustomNotification::class);
    }

    public function unreadNotifications()
    {
        return $this->customNotifications()->unread();
    }

    public function isManager()
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function isRegularUser()
    {
        return $this->role === 'user';
    }

    /**
     * Check if user has Gmail connected
     */
    public function hasGmailConnected(): bool
    {
        return $this->gmail_connected && !empty($this->gmail_token);
    }

    /**
     * Get Gmail service instance for this user
     */
    public function getGmailService()
    {
        if (!$this->hasGmailConnected()) {
            return null;
        }

        return app(\App\Services\GmailOAuthService::class)->getGmailService($this);
    }
}
