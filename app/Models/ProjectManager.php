<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectManager extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'orion_id',
        'email',
        'mobile',
    ];

    /**
     * Get the projects managed by this manager
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    /**
     * Get the full display name with Orion ID
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->orion_id})";
    }
}
