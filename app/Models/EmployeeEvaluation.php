<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'evaluated_by',
        'evaluation_type',
        'evaluation_period_start',
        'evaluation_period_end',
        'performance_score',
        'tasks_completed',
        'on_time_completion_rate',
        'quality_score',
        'early_completions',
        'overdue_tasks',
        'rejected_tasks',
        'rank',
        'manager_notes',
        'employee_notes',
        'status',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'evaluation_period_start' => 'date',
        'evaluation_period_end' => 'date',
        'performance_score' => 'decimal:2',
        'on_time_completion_rate' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function scopeForPeriod($query, $type, $startDate, $endDate)
    {
        return $query->where('evaluation_type', $type)
                    ->where('evaluation_period_start', $startDate)
                    ->where('evaluation_period_end', $endDate);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRanked($query)
    {
        return $query->whereNotNull('rank')->orderBy('rank');
    }

    public function getPerformanceGradeAttribute()
    {
        if ($this->performance_score >= 90) return 'A+';
        if ($this->performance_score >= 80) return 'A';
        if ($this->performance_score >= 70) return 'B+';
        if ($this->performance_score >= 60) return 'B';
        if ($this->performance_score >= 50) return 'C';
        return 'D';
    }

    public function getIsOverdueAttribute()
    {
        $endDate = Carbon::parse($this->evaluation_period_end);
        return $endDate->addDays(7)->isPast() && $this->status !== 'approved';
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->tasks_completed == 0) return 0;
        return round(($this->tasks_completed / ($this->tasks_completed + $this->overdue_tasks)) * 100, 2);
    }
}
