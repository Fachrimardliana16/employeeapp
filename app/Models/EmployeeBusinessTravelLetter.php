<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBusinessTravelLetter extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'registration_number',
        'start_date',
        'end_date',
        'employee_id',
        'destination',
        'destination_detail',
        'purpose_of_trip',
        'business_trip_expenses',
        'pasal',
        'employee_signatory_id',
        'additional_employees',
        'additional_employee_ids',
        'description',
        'signatory_employee_id',
        'signatory_name',
        'signatory_position',
        'pdf_file_path',
        'users_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'business_trip_expenses' => 'decimal:2',
        'additional_employee_ids' => 'array',
    ];

    /**
     * Get the employee that owns the business travel letter.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the additional employees for this travel.
     */
    public function additionalEmployees()
    {
        if (empty($this->additional_employee_ids)) {
            return collect();
        }
        return Employee::whereIn('id', $this->additional_employee_ids)->get();
    }

    /**
     * Get the employee signatory for this travel.
     */
    public function signatory(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_signatory_id');
    }

    /**
     * Get the new signatory employee.
     */
    public function signatoryEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'signatory_employee_id');
    }

    /**
     * Get the user who created this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
