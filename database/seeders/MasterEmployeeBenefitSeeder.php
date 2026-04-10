<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeeBenefit;
use App\Models\User;

class MasterEmployeeBenefitSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $benefits = [
            ['name' => 'Tunjangan Beras', 'desc' => 'Tunjangan untuk kebutuhan beras'],
            ['name' => 'Tunjangan Jabatan', 'desc' => 'Tunjangan sesuai jabatan'],
            ['name' => 'Tunjangan Keluarga', 'desc' => 'Tunjangan untuk keluarga'],
            ['name' => 'Tunjangan Kesehatan', 'desc' => 'Tunjangan kesehatan Pegawai'],
            ['name' => 'Tunjangan Air', 'desc' => 'Tunjangan untuk kebutuhan air'],
            ['name' => 'Tunjangan DPLK', 'desc' => 'Dana Pensiun Lembaga Keuangan'],
            ['name' => 'TKK', 'desc' => 'Tunjangan Kinerja Karyawan'],
        ];

        foreach ($benefits as $benefit) {
            MasterEmployeeBenefit::updateOrCreate(
                ['name' => $benefit['name']],
                [
                    'desc' => $benefit['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
