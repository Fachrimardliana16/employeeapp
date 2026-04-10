<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeeEducation;
use App\Models\User;

class MasterEmployeeEducationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $educations = [
            ['name' => 'SD', 'desc' => 'Sekolah Dasar'],
            ['name' => 'SMP', 'desc' => 'Sekolah Menengah Pertama'],
            ['name' => 'SMA', 'desc' => 'Sekolah Menengah Atas'],
            ['name' => 'D1', 'desc' => 'Diploma 1'],
            ['name' => 'D3', 'desc' => 'Diploma 3'],
            ['name' => 'D4', 'desc' => 'Diploma 4'],
            ['name' => 'S1', 'desc' => 'Sarjana Strata 1'],
            ['name' => 'S2', 'desc' => 'Sarjana Strata 2 (Magister)'],
            ['name' => 'S3', 'desc' => 'Sarjana Strata 3 (Doktor)'],
        ];

        foreach ($educations as $education) {
            MasterEmployeeEducation::updateOrCreate(
                ['name' => $education['name']],
                [
                    'desc' => $education['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
