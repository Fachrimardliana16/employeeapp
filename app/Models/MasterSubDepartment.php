<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivityTrait;

class MasterSubDepartment extends Model
{
    use LogsActivityTrait;
    use HasUserTracking;
    protected $fillable = [
        'departments_id',
        'name',
        'desc',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'departments_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'sub_department_id');
    }
}
