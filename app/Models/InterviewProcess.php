<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewProcess extends Model
{
    protected $fillable = [
        'job_application_id',
        'interview_stage',
        'interview_type',
        'interview_date',
        'interview_time',
        'interview_location',
        'interviewer_name',
        'interviewer_id',
        'result',
        'score',
        'notes',
        'feedback',
        'status',
        'users_id',
    ];

    protected $casts = [
        'interview_date' => 'date',
    ];

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public static function getInterviewTypeOptions(): array
    {
        return [
            'HR Interview' => 'HR Interview',
            'User Interview' => 'User Interview',
            'Psikotes' => 'Psikotes',
            'Medical Check Up' => 'Medical Check Up',
            'Final Interview' => 'Final Interview',
        ];
    }

    public static function getResultOptions(): array
    {
        return [
            'pending' => 'Menunggu',
            'passed' => 'Lulus',
            'failed' => 'Tidak Lulus',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'scheduled' => 'Terjadwal',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }
}
