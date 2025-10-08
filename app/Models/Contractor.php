<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'company',        // Old field (backward compatibility)
        'phone',          // Old field (backward compatibility)
        'mobile',         // New field
        'position',       // New field
        'company_name',   // New field
        'type',           // New field
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => 'string',
        ];
    }

    /**
     * Scope to filter by contractor type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get only clients
     */
    public function scopeClients($query)
    {
        return $query->where('type', 'client');
    }

    /**
     * Scope to get only consultants
     */
    public function scopeConsultants($query)
    {
        return $query->where('type', 'consultant');
    }

    // Task relationships
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'contractor_task')
                    ->withPivot('role', 'added_at')
                    ->withTimestamps();
    }

    public function approvedTasks()
    {
        return $this->tasks()->where('status', 'completed');
    }

    public function pendingTasks()
    {
        return $this->tasks()->whereIn('status', ['pending', 'assigned', 'in_progress', 'submitted_for_review']);
    }

    // Get approved tasks for this contractor on specific project
    public function getApprovedTasksForProject($projectId)
    {
        return $this->tasks()
                    ->where('project_id', $projectId)
                    ->where('status', 'completed')
                    ->get();
    }
}
