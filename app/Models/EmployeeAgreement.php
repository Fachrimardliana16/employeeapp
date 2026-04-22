<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\LogsActivityTrait;

class EmployeeAgreement extends Model
{
    use HasUserTracking, LogsActivityTrait;

    protected $table = 'employee_agreement';

    protected $fillable = [
        'job_application_archives_id',
        'employees_id',
        'agreement_number',
        'name',
        'place_birth',
        'date_birth',
        'gender',
        'marital_status',
        'address',
        'phone_number',
        'email',
        'agreement_id',
        'employee_position_id',
        'employment_status_id',
        'basic_salary_id',
        'employee_education_id',
        'agreement_date_start',
        'agreement_date_end',
        'departments_id',
        'sub_department_id',
        'non_permanent_salary_id',
        'docs',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'date_birth'           => 'date',
        'agreement_date_start' => 'date',
        'agreement_date_end'   => 'date',
        'is_active'            => 'boolean',
    ];

    /**
     * Get the master agreement type.
     */
    public function masterAgreement(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeAgreement::class, 'agreement_id');
    }

    /**
     * Get the employee position.
     */
    public function employeePosition(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeePosition::class, 'employee_position_id');
    }

    /**
     * Get the employment status.
     */
    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeStatusEmployment::class, 'employment_status_id');
    }

    /**
     * Get the basic salary grade.
     */
    public function basicSalaryGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'basic_salary_id');
    }

    /**
     * Get the education level.
     */
    public function education(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeEducation::class, 'employee_education_id');
    }

    /**
     * Get the department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'departments_id');
    }

    /**
     * Get the sub department.
     */
    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterSubDepartment::class, 'sub_department_id');
    }

    /**
     * Get the non-permanent salary link.
     */
    public function nonPermanentSalary(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeNonPermanentSalary::class, 'non_permanent_salary_id');
    }

    /**
     * Get the user who created this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    /**
     * Get the linked Employee (direct link, used for appointment-based contracts).
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }

    /**
     * Get the job application archive.
     */
    public function jobApplicationArchive(): BelongsTo
    {
        return $this->belongsTo(JobApplicationArchive::class, 'job_application_archives_id');
    }

    /**
     * Get the basic salary amount from the grade relationship.
     */
    public function getBasicSalaryAttribute(): float
    {
        if ($this->basic_salary_id) {
            return $this->basicSalaryGrade?->basic_salary ?? 0;
        }

        return $this->nonPermanentSalary?->amount ?? 0;
    }

    /**
     * Get formatted basic salary for display.
     */
    public function getFormattedBasicSalaryAttribute(): string
    {
        return 'Rp ' . number_format($this->basic_salary, 0, ',', '.');
    }

    /**
     * Get the contract duration in years.
     */
    public function getContractDurationAttribute(): int
    {
        if (!$this->agreement_date_start || !$this->agreement_date_end) {
            return 0;
        }

        return $this->agreement_date_start->diffInYears($this->agreement_date_end);
    }

    /**
     * Check if the contract is still active.
     * NOTE: is_active is now a real database column — managed explicitly.
     * This accessor is kept for backward compatibility only when column is null.
     */

    /**
     * Get days remaining in contract.
     */
    public function getDaysRemainingAttribute(): int
    {
        if (!$this->agreement_date_end) {
            return 0;
        }

        return max(0, now()->diffInDays($this->agreement_date_end, false));
    }

    /**
     * Get the full URL for the document.
     */
    public function getDocsUrlAttribute(): ?string
    {
        return $this->docs ? url('image-view/' . $this->docs) : null;
    }

    /**
     * Check if document exists.
     */
    public function getHasDocumentAttribute(): bool
    {
        return !empty($this->docs);
    }

    // Method untuk mengisi data dari lamaran
    public static function createFromJobApplication(JobApplicationArchive $archive): self
    {
        $jobApp = $archive->jobApplication;

        return static::create([
            'job_application_archives_id' => $archive->id,
            'agreement_number' => static::generateAgreementNumber(),
            'name' => $jobApp->name,
            'place_birth' => $jobApp->place_birth,
            'date_birth' => $jobApp->date_birth,
            'gender' => $jobApp->gender,
            'marital_status' => $jobApp->marital_status,
            'address' => $jobApp->address,
            'phone_number' => $jobApp->phone_number,
            'email' => $jobApp->email,
            'agreement_id' => $archive->proposed_agreement_type_id,
            'employee_position_id' => $jobApp->applied_position_id,
            'employment_status_id' => $archive->proposed_employment_status_id,
            'basic_salary_id' => $archive->proposed_grade_id,
            'non_permanent_salary_id' => $archive->proposed_non_permanent_salary_id,
            'employee_education_id' => $jobApp->education_level_id,
            'agreement_date_start' => $archive->proposed_start_date,
            'departments_id' => $jobApp->applied_department_id,
            'sub_department_id' => $jobApp->applied_sub_department_id,
            'users_id' => auth()->id(),
        ]);
    }

    public static function generateAgreementNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastNumber = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return sprintf('PKT-%s%s-%04d', $year, $month, $lastNumber);
    }

    /**
     * SCOPES
     */
    public function scopeDueToExpireInYear($query, $year)
    {
        return $query->where('is_active', true)
            ->whereYear('agreement_date_end', $year);
    }
}
