<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivityTrait;

class MasterPromotionType extends Model
{
    use SoftDeletes, LogsActivityTrait;

    protected $fillable = [
        'code',
        'name',
        'description',
        'requirements',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function promotions()
    {
        return $this->hasMany(EmployeeGradePromotion::class, 'promotion_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
