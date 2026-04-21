<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterEmployeePermission extends Model
{
    use HasUserTracking, LogsActivityTrait;
    protected $fillable = [
        'permission_type_name',
        'permission_description',
        'max_days_allowed',
        'is_paid',
        'is_active',
        'users_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'master_employee_permission_id');
    }
}
