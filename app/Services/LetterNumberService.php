<?php

namespace App\Services;

use App\Models\EmployeeAssignmentLetter;
use App\Models\EmployeeBusinessTravelLetter;
use Carbon\Carbon;

class LetterNumberService
{
    /**
     * Generate registration number for Surat Tugas.
     * Format: [nomor urut]/ST/KEPEG/PDAM/[BULAN ROMAWI]/[TAHUN]
     */
    public static function generateAssignmentNumber(): string
    {
        $year = Carbon::now()->year;
        $monthRoman = self::getRomanMonth(Carbon::now()->month);

        $count = EmployeeAssignmentLetter::whereYear('created_at', $year)->count();
        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "{$sequence}/ST/KEPEG/PDAM/{$monthRoman}/{$year}";
    }

    /**
     * Generate registration number for SPPD.
     * Format: [nomor urut]/SPPD/DIR/PERUMDAMTP/[BULAN ROMAWI]/[TAHUN]
     */
    public static function generateBusinessTravelNumber(): string
    {
        $year = Carbon::now()->year;
        $monthRoman = self::getRomanMonth(Carbon::now()->month);

        $count = EmployeeBusinessTravelLetter::whereYear('created_at', $year)->count();
        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "{$sequence}/SPPD/DIR/PERUMDAMTP/{$monthRoman}/{$year}";
    }

    /**
     * Convert month number to Roman numeral.
     */
    private static function getRomanMonth(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        return $map[$month] ?? 'I';
    }
}
