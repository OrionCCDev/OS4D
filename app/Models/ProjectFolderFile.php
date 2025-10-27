<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFolderFile extends Model
{
    use HasFactory;

    protected $table = 'project_folder_files';

    protected $fillable = [
        'project_id',
        'folder_id',
        'uploaded_by',
        'original_name',
        'display_name',
        'description',
        'mime_type',
        'size_bytes',
        'disk',
        'path',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function folder()
    {
        return $this->belongsTo(ProjectFolder::class, 'folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL to the file
     */
    public function getUrlAttribute()
    {
        return url(str_replace('\\', '/', $this->path));
    }

    /**
     * Get human readable file size
     */
    public function getHumanReadableSizeAttribute()
    {
        $bytes = $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
