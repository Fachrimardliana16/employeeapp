<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

use App\Traits\LogsActivityTrait;

class Employee extends Model
{
    use HasUserTracking, LogsActivityTrait;
    protected $fillable = [
        'nippam',
        'name',
        'place_birth',
        'date_birth',
        'gender',
        'religion',
        'age',
        'address',
        'blood_type',
        'marital_status',
        'phone_number',
        'id_number',
        'familycard_number',
        'npwp_number',
        'bank_account_number',
        'bpjs_tk_number',
        'bpjs_tk_status',
        'bpjs_kes_number',
        'bpjs_kes_status',
        'bpjs_kes_class',
        'rek_dplk_pribadi',
        'rek_dplk_bersama',
        'dapenma_number',
        'dapenma_phdp',
        'dapenma_status',
        'username',
        'email',
        'office_email',
        'password',
        'image',
        'leave_balance',
        'entry_date',
        'probation_appointment_date',
        'permanent_appointment_date',
        'length_service',
        'retirement',
        'employment_status_id',
        'master_employee_agreement_id',
        'agreement_date_start',
        'agreement_date_end',
        'employee_education_id',
        'grade_date_start',
        'grade_date_end',
        'basic_salary_id',
        'periodic_salary_date_start',
        'periodic_salary_date_end',
        'employee_position_id',
        'departments_id',
        'sub_department_id',
        'bagian_id',
        'cabang_id',
        'unit_id',
        'employee_service_grade_id',
        'non_permanent_salary_id',
        'users_id',
    ];

    protected $casts = [
        'date_birth' => 'date',
        'entry_date' => 'date',
        'probation_appointment_date' => 'date',
        'permanent_appointment_date' => 'date',
        'grade_date_start' => 'date',
        'agreement_date_start' => 'date',
        'agreement_date_end' => 'date',
        'grade_date_start' => 'date',
        'grade_date_end' => 'date',
        'periodic_salary_date_start' => 'date',
        'periodic_salary_date_end' => 'date',
        'id_number' => 'encrypted',
        'familycard_number' => 'encrypted',
        'npwp_number' => 'encrypted',
        'bank_account_number' => 'encrypted',
        'bpjs_tk_number' => 'encrypted',
        'bpjs_kes_number' => 'encrypted',
        'dapenma_phdp' => 'float',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
    ];

    // Mutator untuk kolom decimal agar string kosong dikonversi ke null
    public function setDapenmaPhdpAttribute($value): void
    {
        $this->attributes['dapenma_phdp'] = ($value === '' || $value === null) ? null : $value;
    }

    // Boot method untuk auto-generate fields
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            $employee->generateAutomaticFields();
        });

        static::updating(function ($employee) {
            $employee->generateAutomaticFields();
        });
    }

    /**
     * Generate automatic fields
     */
    protected function generateAutomaticFields()
    {
        // Auto-generate age and retirement date (56 years from birth date)
        if ($this->date_birth) {
            $this->retirement = $this->date_birth->copy()->addYears(56);
            $this->age = $this->date_birth->age;
        }

        // Auto-generate username from name slug
        if ($this->name && !$this->username) {
            $baseUsername = Str::slug($this->name, '');
            $username = $baseUsername;
            $counter = 1;

            // Check for duplicates and add number if needed
            while (static::where('username', $username)->where('id', '!=', $this->id ?? 0)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $this->username = $username;
        }

        // Auto-generate NIPPAM if empty
        if (!$this->nippam) {
            $this->nippam = static::generateNippam();
        }
    }

    /**
     * Generate unique NIPPAM
     */
    public static function generateNippam(): string
    {
        $prefix = date('ym');
        $lastEmployee = static::where('nippam', 'LIKE', $prefix . '%')
            ->whereRaw('length(nippam) = 9')
            ->orderBy('nippam', 'desc')
            ->first();

        if (!$lastEmployee) {
            return $prefix . '00001';
        }

        $lastNumber = intval(substr($lastEmployee->nippam, 4));
        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeStatusEmployment::class, 'employment_status_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeAgreement::class, 'master_employee_agreement_id');
    }

    public function education(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeEducation::class, 'employee_education_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'basic_salary_id');
    }

    public function serviceGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeServiceGrade::class, 'employee_service_grade_id');
    }

    public function nonPermanentSalary(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeNonPermanentSalary::class, 'non_permanent_salary_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeePosition::class, 'employee_position_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'departments_id');
    }

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterSubDepartment::class, 'sub_department_id');
    }

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'bagian_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'cabang_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'unit_id');
    }

    // One-to-Many Relationships
    public function promotions(): HasMany
    {
        return $this->hasMany(EmployeePromotion::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(EmployeeMutation::class);
    }

    public function careerMovements(): HasMany
    {
        return $this->hasMany(EmployeeCareerMovement::class, 'employee_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(EmployeeAppointment::class, 'employee_id');
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function families(): HasMany
    {
        return $this->hasMany(EmployeeFamily::class, 'employees_id');
    }

    public function employeeAgreements(): HasMany
    {
        return $this->hasMany(EmployeeAgreement::class, 'email', 'email');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(EmployeeAttendanceRecord::class, 'pin', 'pin');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class, 'employee_id');
    }

    public function assignmentLetters(): HasMany
    {
        return $this->hasMany(EmployeeAssignmentLetter::class, 'assigning_employee_id');
    }

    public function businessTravelLetters(): HasMany
    {
        return $this->hasMany(EmployeeBusinessTravelLetter::class, 'employee_id');
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(EmployeeDailyReport::class, 'employee_id');
    }

    public function employeePermissions(): HasMany
    {
        return $this->hasMany(EmployeePermission::class, 'employee_id');
    }

    /**
     * Get the job application associated with this employee's email.
     */
    public function jobApplication(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JobApplication::class, 'email', 'email');
    }

    /**
     * Get the interview processes associated with this employee's applications.
     */
    public function interviewProcesses(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            InterviewProcess::class,
            JobApplication::class,
            'email',
            'job_application_id',
            'email',
            'id'
        );
    }

    // Helper Methods
    public function getLatestPromotionAttribute()
    {
        return $this->promotions()->latest('promotion_date')->first();
    }

    public function getCurrentSalaryAttribute()
    {
        return $this->salaries()->latest()->first();
    }

    public function getRemainingLeaveBalanceAttribute(): int
    {
        $usedLeave = $this->employeePermissions()
            ->whereHas('permission', function ($query) {
                $query->where('permission_type_name', 'LIKE', '%cuti%')
                    ->orWhere('permission_type_name', 'LIKE', '%leave%');
            })
            ->where('approval_status', 'approved')
            ->whereYear('start_permission_date', now()->year)
            ->selectRaw('SUM(julianday(end_permission_date) - julianday(start_permission_date) + 1) as total_days')
            ->value('total_days') ?? 0;

        return max(0, ($this->leave_balance ?? 12) - $usedLeave);
    }

    /**
     * Get length of service in months (auto-calculated from probation appointment date)
     */
    public function getLengthServiceAttribute(): ?int
    {
        if (!$this->permanent_appointment_date) {
            return null;
        }

        return $this->permanent_appointment_date->diffInMonths(now());
    }

    /**
     * Get formatted length of service
     */
    public function getFormattedLengthServiceAttribute(): string
    {
        $months = $this->length_service;

        if (is_null($months)) {
            return 'Belum ada data';
        }

        if ($months === 0) {
            return '0 bulan';
        }

        $years = intval($months / 12);
        $remainingMonths = $months % 12;

        if ($years > 0 && $remainingMonths > 0) {
            return "{$years} tahun {$remainingMonths} bulan";
        } elseif ($years > 0) {
            return "{$years} tahun";
        } else {
            return "{$remainingMonths} bulan";
        }
    }

    /**
     * Check if employee has incomplete personal data.
     */
    public function hasIncompleteData(): bool
    {
        // Hanya cek data yang memang penting dan biasanya kosong (exclude otomatis fields)
        $importantEmptyFields = [
            'id_number',
            'familycard_number',
            'bank_account_number',
            'bpjs_tk_number',
            'bpjs_tk_status',
            'bpjs_kes_number',
            'bpjs_kes_status',
            'bpjs_kes_class',
            'rek_dplk_pribadi',
            'rek_dplk_bersama',
            'dapenma_number',
            'dapenma_phdp',
            'dapenma_status',
            'employee_education_id',
            'probation_appointment_date',
            // retirement, username, length_service dihilangkan karena otomatis
        ];

        foreach ($importantEmptyFields as $field) {
            if (empty($this->$field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of missing data fields.
     */
    public function getMissingDataFields(): array
    {
        $importantEmptyFields = [
            'id_number' => 'ID Number (KTP)',
            'familycard_number' => 'Family Card Number',
            'bank_account_number' => 'Bank Account Number',
            'bpjs_kes_number' => 'BPJS Kesehatan',
            'bpjs_kes_status' => 'Status BPJS Kesehatan',
            'bpjs_kes_class' => 'Kelas BPJS Kesehatan',
            'bpjs_tk_number' => 'BPJS Ketenagakerjaan',
            'bpjs_tk_status' => 'Status BPJS Ketenagakerjaan',
            'rek_dplk_pribadi' => 'DPLK Pribadi',
            'rek_dplk_bersama' => 'DPLK Bersama',
            'dapenma_number' => 'Nomor Dapenma',
            'dapenma_phdp' => 'PHDP Dapenma',
            'dapenma_status' => 'Status Dapenma',
            'employee_education_id' => 'Education Level',
            'probation_appointment_date' => 'Probation Appointment Date',
            // retirement, username, length_service dihilangkan karena otomatis
        ];

        $missing = [];
        foreach ($importantEmptyFields as $field => $label) {
            if (empty($this->$field)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * Get percentage of data completeness.
     */
    public function getDataCompletenessPercentage(): int
    {
        $importantFields = [
            'id_number',
            'familycard_number',
            'bank_account_number',
            'bpjs_kes_number',
            'bpjs_kes_status',
            'bpjs_kes_class',
            'bpjs_tk_number',
            'bpjs_tk_status',
            'rek_dplk_pribadi',
            'rek_dplk_bersama',
            'dapenma_number',
            'dapenma_phdp',
            'dapenma_status',
            'employee_education_id',
            'probation_appointment_date'
            // retirement, username, length_service dihilangkan karena otomatis
        ];

        $completedFields = 0;
        foreach ($importantFields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($importantFields)) * 100);
    }

    /**
     * Get employees who can be signatories (Directors, Department Heads, etc.)
     */
    public static function getSignatoryEmployees()
    {
        return static::with('position')
            ->whereHas('position', function ($query) {
                $query->whereNotIn('name', [
                    'Kepala Sub Bagian',
                    'Kordinator Lapangan',
                    'Staff',
                    'Staf',
                    'Kepala SubBagian'
                ])
                    ->where('name', 'NOT LIKE', '%Staff%')
                    ->where('name', 'NOT LIKE', '%Staf%');
            })
            ->orderBy('name')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'position' => $employee->position->name ?? '',
                    'label' => $employee->name . ' (' . ($employee->position->name ?? 'Tidak ada jabatan') . ')'
                ];
            })
            ->pluck('label', 'id')
            ->toArray();
    }

    /**
     * Get signatory employee by ID with position
     */
    public static function getSignatoryById($id)
    {
        return static::with('position')->find($id);
    }

    public function getActiveOrganizationalUnitAttribute()
    {
        return $this->unit ?? $this->cabang ?? $this->bagian ?? $this->department;
    }

    /**
     * Get basic salary amount based on grade and service grade
     */
    public function getBasicSalaryAmountAttribute()
    {
        if ($this->basic_salary_id && $this->employee_service_grade_id) {
            return \App\Models\MasterEmployeeBasicSalary::where('employee_grade_id', $this->basic_salary_id)
                ->where('employee_service_grade_id', $this->employee_service_grade_id)
                ->value('amount') ?? 0;
        }

        return $this->nonPermanentSalary?->amount ?? 0;
    }
}
