<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'project_manager_id',
        'name',
        'short_code',
        'description',
        'status',
        'start_date',
        'due_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'end_date' => 'date',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function projectManager()
    {
        return $this->belongsTo(ProjectManager::class, 'project_manager_id');
    }

    public function folders()
    {
        return $this->hasMany(ProjectFolder::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->wherePivot('role', 'member')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function leads()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->wherePivot('role', 'lead')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function observers()
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->wherePivot('role', 'observer')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    // Contractor relationships
    public function contractors()
    {
        return $this->belongsToMany(Contractor::class, 'project_contractor')
                    ->withPivot('role', 'assigned_at')
                    ->withTimestamps();
    }

    public function clientContractors()
    {
        return $this->contractors()->wherePivot('role', 'client');
    }

    public function consultantContractors()
    {
        return $this->contractors()->wherePivot('role', 'consultant');
    }

    public function orionStaffContractors()
    {
        return $this->contractors()->wherePivot('role', 'orion_staff');
    }

    // Methods to manage project members
    public function addUser(User $user, string $role = 'member')
    {
        if (!$this->users()->where('user_id', $user->id)->exists()) {
            $this->users()->attach($user->id, [
                'role' => $role,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function removeUser(User $user)
    {
        $this->users()->detach($user->id);
    }

    public function updateUserRole(User $user, string $role)
    {
        $this->users()->updateExistingPivot($user->id, [
            'role' => $role,
            'updated_at' => now()
        ]);
    }

    // Methods to manage project contractors
    public function addContractor(Contractor $contractor, ?string $role = null)
    {
        if (!$this->contractors()->where('contractor_id', $contractor->id)->exists()) {
            $this->contractors()->attach($contractor->id, [
                'role' => $role ?? $contractor->type,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function removeContractor(Contractor $contractor)
    {
        $this->contractors()->detach($contractor->id);
    }

    public function updateContractorRole(Contractor $contractor, string $role)
    {
        $this->contractors()->updateExistingPivot($contractor->id, [
            'role' => $role,
            'updated_at' => now()
        ]);
    }

    /**
     * Get the number of days remaining until the end date
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }

        $today = Carbon::now()->startOfDay();
        $endDate = Carbon::parse($this->end_date)->startOfDay();

        if ($endDate->isPast()) {
            return 0;
        }

        return $today->diffInDays($endDate, false);
    }

    /**
     * Get the number of days past the end date
     */
    public function getDaysPastAttribute()
    {
        if (!$this->end_date) {
            return null;
        }

        $today = Carbon::now()->startOfDay();
        $endDate = Carbon::parse($this->end_date)->startOfDay();

        if ($endDate->isFuture()) {
            return 0;
        }

        return $endDate->diffInDays($today, false);
    }

    /**
     * Get the number of days since the start date
     */
    public function getDaysSinceStartAttribute()
    {
        if (!$this->start_date) {
            return null;
        }

        $today = Carbon::now()->startOfDay();
        $startDate = Carbon::parse($this->start_date)->startOfDay();

        if ($startDate->isFuture()) {
            return 0;
        }

        return $startDate->diffInDays($today, false);
    }

    /**
     * Get the project duration in days (creation to due date)
     */
    public function getDurationAttribute()
    {
        if ($this->created_at && $this->due_date) {
            return $this->created_at->diffInDays($this->due_date);
        }
        return null;
    }

    /**
     * Check if the project is overdue
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->end_date || $this->status === 'completed' || $this->status === 'cancelled') {
            return false;
        }

        return Carbon::parse($this->end_date)->isPast();
    }

    /**
     * Create notification for project approaching end date
     */
    public function createEndDateNotification()
    {
        if (!$this->end_date || $this->status === 'completed' || $this->status === 'cancelled') {
            return;
        }

        $daysRemaining = $this->days_remaining;
        $isOverdue = $this->is_overdue;

        if ($isOverdue) {
            // Project is overdue
            $this->owner->customNotifications()->create([
                'type' => 'project_overdue',
                'title' => 'Project Overdue',
                'message' => "Project '{$this->name}' is {$this->days_past} days overdue. Please review and update the project status.",
                'data' => [
                    'project_id' => $this->id,
                    'days_overdue' => $this->days_past
                ]
            ]);
        } elseif ($daysRemaining <= 3 && $daysRemaining > 0) {
            // Project ending soon (3 days or less)
            $this->owner->customNotifications()->create([
                'type' => 'project_ending_soon',
                'title' => 'Project Ending Soon',
                'message' => "Project '{$this->name}' will end in {$daysRemaining} day" . ($daysRemaining === 1 ? '' : 's') . ". Please review and complete remaining tasks.",
                'data' => [
                    'project_id' => $this->id,
                    'days_remaining' => $daysRemaining
                ]
            ]);
        }
    }

    /**
     * Check and create notifications for all projects
     */
    public static function checkAndCreateEndDateNotifications()
    {
        $projects = self::whereNotNull('end_date')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        foreach ($projects as $project) {
            $project->createEndDateNotification();
        }
    }
}


