<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAppointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'decision_letter_number',
        'appointment_date',
        'employee_id',
        'old_employment_status_id',
        'new_employment_status_id',
        'employee_grade_id',
        'docs',
        'desc',
        'users_id',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function oldEmploymentStatus(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeStatusEmployment::class, 'old_employment_status_id');
    }

    public function newEmploymentStatus(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeStatusEmployment::class, 'new_employment_status_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'employee_grade_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
