<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_folders';

    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(ProjectFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProjectFolder::class, 'parent_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'folder_id');
    }
}


