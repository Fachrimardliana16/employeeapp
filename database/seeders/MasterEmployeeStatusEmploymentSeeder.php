<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeeStatusEmployment;
use App\Models\User;

class MasterEmployeeStatusEmploymentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $statuses = [
            ['name' => 'Tenaga Harian Lepas', 'desc' => 'Pegawai harian lepas'],
            ['name' => 'Magang', 'desc' => 'Magang'],
            ['name' => 'Kontrak', 'desc' => 'Pegawai kontrak'],
            ['name' => 'Calon Pegawai', 'desc' => 'Calon pegawai tetap'],
            ['name' => 'Pegawai Tetap', 'desc' => 'Pegawai tetap'],
            ['name' => 'Pensiun', 'desc' => 'Purna Tugas'],
        ];

        foreach ($statuses as $status) {
            MasterEmployeeStatusEmployment::updateOrCreate(
                ['name' => $status['name']],
                [
                    'desc' => $status['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
