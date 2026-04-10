<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeeGrade;
use App\Models\User;

class MasterEmployeeGradeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $grades = [
            'A1', 'A2', 'A3', 'A4',
            'B1', 'B2', 'B3', 'B4',
            'C1', 'C2', 'C3', 'C4',
            'D1', 'D2', 'D3', 'D4'
        ];

        foreach ($grades as $grade) {
            MasterEmployeeGrade::updateOrCreate(
                ['name' => $grade],
                [
                    'desc' => 'Golongan ' . $grade,
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
