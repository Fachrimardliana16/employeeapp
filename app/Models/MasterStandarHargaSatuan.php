<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;

class MasterStandarHargaSatuan extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'code',
        'name',
        'category',
        'location',
        'spesifikasi',
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
    /**
     * Map internal employee position and grade to SHS spesifikasi
     */
    public static function mapPositionToSpesifikasi(string $positionName, ?string $gradeName = null): string
    {
        $name = strtolower($positionName);

        if (str_contains($name, 'direktur')) {
            return 'Direktur / Setda Ketua DPRD';
        }

        if (str_contains($name, 'bagian') || str_contains($name, 'cabang') || str_contains($name, 'spi')) {
            return 'Kepala Bagian / Ka.SPI / Kepala Cabang';
        }

        if (str_contains($name, 'unit') || str_contains($name, 'sub bagian') || str_contains($name, 'amd') || str_contains($name, 'seksi')) {
            return 'Kasubag / Ka Unit / AMD';
        }

        if (str_contains($name, 'staff') || str_contains($name, 'staf') || str_contains($name, 'pengadministrasian')) {
            if ($gradeName) {
                $firstChar = strtoupper(substr($gradeName, 0, 1));
                if ($firstChar === 'C') return 'Staf - Gol III';
                if ($firstChar === 'B') return 'Staf - Gol II / Driver';
                if ($firstChar === 'A') return 'Staf - Gol I / Capeg';
            }
            return 'Staf - Gol III'; // Default for higher staff
        }
        
        if (str_contains($name, 'honorer') || str_contains($name, 'kontrak')) {
            return 'Staf - Kontrak / Honorer';
        }

        if (str_contains($name, 'sopir') || str_contains($name, 'driver') || str_contains($name, 'pengemudi')) {
            return 'Staf - Gol II / Driver';
        }

        if (str_contains($name, 'koordinator')) {
            return 'Kasubag / Ka Unit / AMD'; 
        }

        return $positionName;
    }
}
