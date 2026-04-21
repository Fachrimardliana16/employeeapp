<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBusinessTravelLetter extends Model
{
    use HasUserTracking, LogsActivityTrait, SoftDeletes;

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
        'additional_employees_detail',
        'accommodation_cost',
        'pocket_money_cost',
        'reserve_cost',
        'transport_cost',
        'meal_cost',
        'total_cost',
        'trip_duration_days',
        'total_employees',
        'description',
        'signatory_employee_id',
        'signatory_name',
        'signatory_position',
        'pdf_file_path',
        'signed_file_path',
        'visit_file_path',
        'shs_category',
        'shs_location',
        'accommodation_reserve_cost',
        'status',
        'users_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'business_trip_expenses' => 'decimal:2',
        'additional_employee_ids' => 'array',
        'additional_employees_detail' => 'array',
        'accommodation_cost' => 'decimal:2',
        'pocket_money_cost' => 'decimal:2',
        'reserve_cost' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'meal_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'status' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->registration_number)) {
                $model->registration_number = \App\Services\LetterNumberService::generateBusinessTravelNumber();
            }
            if (empty($model->status)) {
                $model->status = 'on progress';
            }
        });

        static::updating(function ($model) {
            if (!empty($model->visit_file_path)) {
                $model->status = 'selesai';
            }
        });
    }

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
