<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'filters',
        'columns',
        'settings',
        'is_default',
        'is_public',
        'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'settings' => 'array',
        'is_default' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function getIsCustomAttribute()
    {
        return !$this->is_default;
    }

    public function getFilterOptionsAttribute()
    {
        return $this->filters ?? [];
    }

    public function getColumnOptionsAttribute()
    {
        return $this->columns ?? [];
    }

    public function getSettingsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = json_encode($value);
    }
}
