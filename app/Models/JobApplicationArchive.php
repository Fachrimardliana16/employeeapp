<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplicationArchive extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_application_id',
        'application_data',
        'interview_data',
        'decision',
        'decision_reason',
        'decision_date',
        'decided_by',
        'proposed_agreement_type_id',
        'proposed_employment_status_id',
        'proposed_grade_id',
        'proposed_salary',
        'proposed_start_date',
    ];

    protected $casts = [
        'application_data' => 'array',
        'interview_data' => 'array',
        'decision_date' => 'date',
        'proposed_salary' => 'decimal:2',
        'proposed_start_date' => 'date',
    ];

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function employeeAgreement(): HasOne
    {
        return $this->hasOne(EmployeeAgreement::class, 'job_application_archives_id');
    }

    public function proposedAgreementType(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeAgreement::class, 'proposed_agreement_type_id');
    }

    public function proposedEmploymentStatus(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeStatusEmployment::class, 'proposed_employment_status_id');
    }

    public function proposedGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'proposed_grade_id');
    }

    public function decidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function getDecisionLabelAttribute(): string
    {
        return match($this->decision) {
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            default => $this->decision,
        };
    }

    // Create archive from job application
    public static function createFromJobApplication(JobApplication $jobApplication, array $decisionData): self
    {
        return static::create([
            'job_application_id' => $jobApplication->id,
            'application_data' => $jobApplication->toArray(),
            'interview_data' => $jobApplication->interview_results,
            'decision' => $decisionData['decision'],
            'decision_reason' => $decisionData['decision_reason'] ?? null,
            'decision_date' => $decisionData['decision_date'] ?? now()->toDateString(),
            'decided_by' => auth()->id(),
            'proposed_agreement_type_id' => $decisionData['proposed_agreement_type_id'] ?? null,
            'proposed_employment_status_id' => $decisionData['proposed_employment_status_id'] ?? null,
            'proposed_grade_id' => $decisionData['proposed_grade_id'] ?? null,
            'proposed_salary' => $decisionData['proposed_salary'] ?? null,
            'proposed_start_date' => $decisionData['proposed_start_date'] ?? null,
        ]);
    }
}
