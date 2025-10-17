<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'metric_date',
        'period_type',
        'tasks_assigned',
        'tasks_completed',
        'tasks_on_time',
        'tasks_early',
        'tasks_overdue',
        'tasks_rejected',
        'high_priority_tasks',
        'medium_priority_tasks',
        'low_priority_tasks',
        'average_completion_time',
        'efficiency_score',
        'quality_score',
        'punctuality_score',
        'overall_score',
        'rank',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'average_completion_time' => 'decimal:2',
        'efficiency_score' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'punctuality_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeForPeriod($query, $type, $startDate, $endDate)
    {
        return $query->where('period_type', $type)
                    ->whereBetween('metric_date', [$startDate, $endDate]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeRanked($query)
    {
        return $query->whereNotNull('rank')->orderBy('rank');
    }

    public function getCompletionRateAttribute()
    {
        if ($this->tasks_assigned == 0) return 0;
        return round(($this->tasks_completed / $this->tasks_assigned) * 100, 2);
    }

    public function getOnTimeRateAttribute()
    {
        if ($this->tasks_completed == 0) return 0;
        return round(($this->tasks_on_time / $this->tasks_completed) * 100, 2);
    }

    public function getQualityRateAttribute()
    {
        if ($this->tasks_completed == 0) return 0;
        $rejectedRate = ($this->tasks_rejected / $this->tasks_completed) * 100;
        return round(100 - $rejectedRate, 2);
    }

    public function getProductivityScoreAttribute()
    {
        $completionWeight = 0.4;
        $qualityWeight = 0.3;
        $punctualityWeight = 0.3;

        return round(
            ($this->completion_rate * $completionWeight) +
            ($this->quality_rate * $qualityWeight) +
            ($this->on_time_rate * $punctualityWeight),
            2
        );
    }

    public function getPerformanceGradeAttribute()
    {
        $score = $this->overall_score;
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B+';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        return 'D';
    }
}
