<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Employee extends Model
{
    use HasUserTracking;
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
        'bpjs_kes_number',
        'rek_dplk_pribadi',
        'rek_dplk_bersama',
        'username',
        'email',
        'password',
        'image',
        'leave_balance',
        'entry_date',
        'probation_appointment_date',
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
        'users_id',
    ];

    protected $casts = [
        'date_birth' => 'date',
        'entry_date' => 'date',
        'probation_appointment_date' => 'date',
        'retirement' => 'date',
        'agreement_date_start' => 'date',
        'agreement_date_end' => 'date',
        'grade_date_start' => 'date',
        'grade_date_end' => 'date',
        'periodic_salary_date_start' => 'date',
        'periodic_salary_date_end' => 'date',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
    ];

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
        // Auto-generate retirement date (56 years from birth date)
        if ($this->date_birth) {
            $this->retirement = $this->date_birth->copy()->addYears(56);
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
        $prefix = 'NIP-' . date('Ym');
        $lastEmployee = static::where('nippam', 'LIKE', $prefix . '%')
            ->orderBy('nippam', 'desc')
            ->first();

        if (!$lastEmployee) {
            return $prefix . '-0001';
        }

        $lastNumber = intval(substr($lastEmployee->nippam, -4));
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return $prefix . '-' . $newNumber;
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

    // One-to-Many Relationships
    public function promotions(): HasMany
    {
        return $this->hasMany(EmployeePromotion::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(EmployeeMutation::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(EmployeePermission::class, 'employees_id');
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
        $usedLeave = $this->permissions()
            ->whereHas('masterPermission', function ($query) {
                $query->where('permission_type_name', 'LIKE', '%cuti%')
                    ->orWhere('permission_type_name', 'LIKE', '%leave%');
            })
            ->where('permission_status', 'approved')
            ->whereYear('permission_start_date', now()->year)
            ->sum('permission_duration_days');

        return max(0, ($this->leave_balance ?? 12) - $usedLeave);
    }

    /**
     * Get length of service in months (auto-calculated from probation appointment date)
     */
    public function getLengthServiceAttribute(): ?int
    {
        if (!$this->probation_appointment_date) {
            return null;
        }

        return $this->probation_appointment_date->diffInMonths(now());
    }

    /**
     * Get formatted length of service
     */
    public function getFormattedLengthServiceAttribute(): string
    {
        $months = $this->length_service;

        if (!$months) {
            return 'Belum ada data';
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
            'bpjs_kes_number',
            'bpjs_tk_number',
            'rek_dplk_pribadi',
            'rek_dplk_bersama',
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
            'bpjs_tk_number' => 'BPJS Ketenagakerjaan',
            'rek_dplk_pribadi' => 'DPLK Pribadi',
            'rek_dplk_bersama' => 'DPLK Bersama',
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
            'bpjs_tk_number',
            'rek_dplk_pribadi',
            'rek_dplk_bersama',
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
                $query->where(function ($q) {
                    $q->where('name', 'LIKE', '%Direktur%')
                        ->orWhere('name', 'LIKE', '%direktur%')
                        ->orWhere('name', 'LIKE', '%Kepala Bagian%')
                        ->orWhere('name', 'LIKE', '%kepala bagian%')
                        ->orWhere('name', 'LIKE', '%Kepala Divisi%')
                        ->orWhere('name', 'LIKE', '%kepala divisi%')
                        ->orWhere('name', 'LIKE', '%Manager%')
                        ->orWhere('name', 'LIKE', '%manager%')
                        ->orWhere('name', 'LIKE', '%Kepala Seksi%')
                        ->orWhere('name', 'LIKE', '%kepala seksi%')
                        ->orWhere('name', 'LIKE', '%Ketua%')
                        ->orWhere('name', 'LIKE', '%ketua%');
                });
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
}
