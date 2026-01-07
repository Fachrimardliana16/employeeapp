<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;

class MasterStandarHargaSatuan extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'name',
        'category',
        'location',
        'grade_level',
        'amount',
        'unit',
        'description',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get category label
     */
    public static function getCategoryOptions(): array
    {
        return [
            'accommodation' => 'Akomodasi/Penginapan',
            'pocket_money' => 'Uang Saku',
            'reserve' => 'Uang Cadangan',
            'transport' => 'Transportasi',
            'meal' => 'Konsumsi/Makan',
        ];
    }

    /**
     * Get unit options
     */
    public static function getUnitOptions(): array
    {
        return [
            'per_day' => 'Per Hari',
            'per_trip' => 'Per Perjalanan',
            'lump_sum' => 'Lump Sum',
        ];
    }
}
