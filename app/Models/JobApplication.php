<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobApplication extends Model
{
    use SoftDeletes, LogsActivityTrait, HasFactory;

    protected $fillable = [
        'application_number',
        'name',
        'place_birth',
        'date_birth',
        'gender',
        'marital_status',
        'address',
        'phone_number',
        'email',
        'id_number',
        'photo',
        'applied_position_id',
        'applied_department_id',
        'applied_sub_department_id',
        'education_level_id',
        'education_institution',
        'education_major',
        'education_graduation_year',
        'education_gpa',
        'last_company_name',
        'last_position',
        'last_work_start_date',
        'last_work_end_date',
        'last_work_description',
        'last_salary',
        'expected_salary',
        'available_start_date',
        'documents',
        'status',
        'notes',
        'interview_schedule',
        'interview_results',
        'reference_name',
        'reference_phone',
        'reference_relation',
        'submitted_at',
        'reviewed_at',
        'interview_at',
        'decision_at',
    ];

    protected $casts = [
        'date_birth' => 'date',
        'last_work_start_date' => 'date',
        'last_work_end_date' => 'date',
        'available_start_date' => 'date',
        'documents' => 'array',
        'interview_schedule' => 'array',
        'interview_results' => 'array',
        'education_gpa' => 'decimal:2',
        'last_salary' => 'decimal:2',
        'expected_salary' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'interview_at' => 'datetime',
        'decision_at' => 'datetime',
    ];

    // Relationships
    public function appliedPosition(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeePosition::class, 'applied_position_id');
    }

    public function appliedDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'applied_department_id');
    }

    public function appliedSubDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterSubDepartment::class, 'applied_sub_department_id');
    }

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeEducation::class, 'education_level_id');
    }

    public function archive(): HasOne
    {
        return $this->hasOne(JobApplicationArchive::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Auto generate application number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->application_number)) {
                $model->application_number = static::generateApplicationNumber();
            }

            if (empty($model->submitted_at)) {
                $model->submitted_at = now();
            }

            // User tracking
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            // User tracking
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    public static function generateApplicationNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastNumber = static::withTrashed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return sprintf('APP-%s%s-%04d', $year, $month, $lastNumber);
    }

    // Status helpers
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeProcessed(): bool
    {
        return in_array($this->status, ['submitted', 'reviewed', 'interviewed']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'submitted' => 'Baru Dikirim',
            'reviewed' => 'Sedang Direview',
            'interview_scheduled' => 'Dijadwalkan Interview',
            'interviewed' => 'Sudah Interview',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            'withdrawn' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => $this->gender,
        };
    }

    public function getMaritalStatusLabelAttribute(): string
    {
        return match($this->marital_status) {
            'single' => 'Belum Menikah',
            'married' => 'Menikah',
            'divorced' => 'Cerai',
            'widowed' => 'Janda/Duda',
            default => $this->marital_status,
        };
    }
}
