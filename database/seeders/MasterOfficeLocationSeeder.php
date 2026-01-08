<?php

namespace Database\Seeders;

use App\Models\MasterOfficeLocation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterOfficeLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first(); // Get first user as creator

        $locations = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'code' => 'JKT-HQ',
                'address' => 'Jl. Jenderal Sudirman No. 1, Jakarta Pusat',
                'latitude' => -6.208763,
                'longitude' => 106.845599,
                'radius' => 100,
                'description' => 'Kantor pusat perusahaan di Jakarta',
                'is_active' => true,
            ],
            [
                'name' => 'Kantor Cabang Bandung',
                'code' => 'BDG-01',
                'address' => 'Jl. Asia Afrika No. 100, Bandung',
                'latitude' => -6.921608,
                'longitude' => 107.607140,
                'radius' => 75,
                'description' => 'Kantor cabang Bandung',
                'is_active' => true,
            ],
            [
                'name' => 'Kantor Cabang Surabaya',
                'code' => 'SBY-01',
                'address' => 'Jl. Raya Darmo No. 50, Surabaya',
                'latitude' => -7.266548,
                'longitude' => 112.740959,
                'radius' => 50,
                'description' => 'Kantor cabang Surabaya',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            MasterOfficeLocation::create(array_merge($location, [
                'users_id' => $adminUser?->id ?? 1,
            ]));
        }

        $this->command->info('Sample office locations created successfully!');
    }
}
