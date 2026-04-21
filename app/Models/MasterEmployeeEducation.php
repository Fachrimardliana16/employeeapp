<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterEmployeeEducation extends Model
{
    use HasUserTracking, LogsActivityTrait;
    protected $table = 'master_employee_education';

    protected $fillable = [
        'name',
        'desc',
        'is_active',
        'users_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'employee_education_id');
    }
}
