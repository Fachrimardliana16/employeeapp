<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterEmployeePositionSalaryCut extends Model
{
    use HasUserTracking, LogsActivityTrait;

    protected $table = 'master_employee_position_salary_cuts';

    protected $fillable = [
        'employee_position_id',
        'salary_cuts_id',
        'amount',
        'desc',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeePosition::class, 'employee_position_id');
    }

    public function salaryCut(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeSalaryCut::class, 'salary_cuts_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
