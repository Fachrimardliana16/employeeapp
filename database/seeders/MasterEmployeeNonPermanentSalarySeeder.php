<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeeNonPermanentSalary;

class MasterEmployeeNonPermanentSalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'UMK Purbalingga 2026 (Kontrak)',
                'employment_status_id' => 3, // Kontrak
                'amount' => 2474722,
                'is_active' => true,
            ],
            [
                'name' => 'Gaji Magang Harian',
                'employment_status_id' => 2, // Magang
                'amount' => 80000,
                'is_active' => true,
            ],
            [
                'name' => 'Gaji THL Harian',
                'employment_status_id' => 1, // THL
                'amount' => 120000,
                'is_active' => true,
            ],
        ];

        foreach ($data as $item) {
            MasterEmployeeNonPermanentSalary::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
