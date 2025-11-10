<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
        'status',
        'deactivated_at',
        'deactivation_reason',
        'img',
        'mobile',
        'position',
        'notification_sound_enabled',
        'gmail_token',
        'gmail_refresh_token',
        'gmail_access_token',
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
            'deactivated_at' => 'datetime',
        ];
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope to only get active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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

    public function unifiedNotifications()
    {
        return $this->hasMany(UnifiedNotification::class);
    }

    public function unreadNotifications()
    {
        return $this->customNotifications()->unread();
    }

    // Project relationships
    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function projectsAsLead()
    {
        return $this->belongsToMany(Project::class, 'project_user')
                    ->wherePivot('role', 'lead')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function isManager()
    {
        return in_array($this->role, ['admin', 'manager', 'sub-admin', 'sup-admin']);
    }

    public function isSubAdmin()
    {
        return $this->role === 'sub-admin';
    }

    public function isSupAdmin()
    {
        return $this->role === 'sup-admin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isRegularUser()
    {
        return $this->role === 'user';
    }

    /**
     * Check if user can delete anything
     * Sub-admin cannot delete anything
     */
    public function canDelete()
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /**
     * Check if user can view all sections
     * Sub-admin can only view projects and tasks
     */
    public function canViewAll()
    {
        return in_array($this->role, ['admin', 'manager', 'sup-admin']);
    }

    /**
     * Check if user has full admin privileges
     * Sub-admin has most privileges except delete and view restrictions
     */
    public function hasFullPrivileges()
    {
        return in_array($this->role, ['admin', 'manager', 'sub-admin', 'sup-admin']);
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
