<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterDepartment extends Model
{
    use HasUserTracking;

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
        return $this->hasMany(Employee::class, 'departments_id');
    }

    public function subDepartmentEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'sub_department_id');
    }

    public function subDepartments(): HasMany
    {
        return $this->hasMany(MasterSubDepartment::class, 'departments_id');
    }
}
