<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceAppraisal extends Model
{
    protected $fillable = [
        'employee_id',
        'appraisal_period',
        'appraisal_date',
        'appraiser_id',
        'criteria_scores',
        'total_score',
        'performance_grade',
        'strengths',
        'weaknesses',
        'recommendations',
        'employee_comment',
        'status',
        'approved_by',
        'approved_at',
        'users_id',
    ];

    protected $casts = [
        'appraisal_date' => 'date',
        'criteria_scores' => 'array',
        'total_score' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function appraiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appraiser_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public static function getGradeOptions(): array
    {
        return [
            'A' => 'A - Sangat Baik (90-100)',
            'B' => 'B - Baik (80-89)',
            'C' => 'C - Cukup (70-79)',
            'D' => 'D - Kurang (60-69)',
            'E' => 'E - Sangat Kurang (<60)',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'reviewed' => 'Ditinjau',
            'approved' => 'Disetujui',
        ];
    }

    public function calculateGrade(): void
    {
        $score = $this->total_score;

        if ($score >= 90) {
            $this->performance_grade = 'A';
        } elseif ($score >= 80) {
            $this->performance_grade = 'B';
        } elseif ($score >= 70) {
            $this->performance_grade = 'C';
        } elseif ($score >= 60) {
            $this->performance_grade = 'D';
        } else {
            $this->performance_grade = 'E';
        }
    }
}
