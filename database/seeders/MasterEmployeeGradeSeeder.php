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

        // Data dari PP Perusahaan Pasal 8
        $grades = [
            // Golongan A
            ['name' => 'A1', 'pangkat_name' => 'Pegawai Dasar Muda', 'desc' => 'Golongan A ruang 1'],
            ['name' => 'A2', 'pangkat_name' => 'Pegawai Dasar Muda I', 'desc' => 'Golongan A ruang 2'],
            ['name' => 'A3', 'pangkat_name' => 'Pegawai Dasar', 'desc' => 'Golongan A ruang 3'],
            ['name' => 'A4', 'pangkat_name' => 'Pegawai Dasar I', 'desc' => 'Golongan A ruang 4'],
            
            // Golongan B
            ['name' => 'B1', 'pangkat_name' => 'Pelaksana Muda', 'desc' => 'Golongan B ruang 1'],
            ['name' => 'B2', 'pangkat_name' => 'Pelaksana Muda I', 'desc' => 'Golongan B ruang 2'],
            ['name' => 'B3', 'pangkat_name' => 'Pelaksana', 'desc' => 'Golongan B ruang 3'],
            ['name' => 'B4', 'pangkat_name' => 'Pelaksana I', 'desc' => 'Golongan B ruang 4'],
            
            // Golongan C
            ['name' => 'C1', 'pangkat_name' => 'Staf Muda', 'desc' => 'Golongan C ruang 1'],
            ['name' => 'C2', 'pangkat_name' => 'Staf Muda I', 'desc' => 'Golongan C ruang 2'],
            ['name' => 'C3', 'pangkat_name' => 'Staf', 'desc' => 'Golongan C ruang 3'],
            ['name' => 'C4', 'pangkat_name' => 'Staf I', 'desc' => 'Golongan C ruang 4'],
            
            // Golongan D
            ['name' => 'D1', 'pangkat_name' => 'Staf Madya', 'desc' => 'Golongan D ruang 1'],
            ['name' => 'D2', 'pangkat_name' => 'Staf Madya I', 'desc' => 'Golongan D ruang 2'],
            ['name' => 'D3', 'pangkat_name' => 'Staf Utama Madya', 'desc' => 'Golongan D ruang 3'],
            ['name' => 'D4', 'pangkat_name' => 'Staf Utama', 'desc' => 'Golongan D ruang 4'],
            ['name' => 'D5', 'pangkat_name' => null, 'desc' => 'Golongan D ruang 5 (tidak ada di PP Pasal 8)'],
        ];

        foreach ($grades as $grade) {
            MasterEmployeeGrade::updateOrCreate(
                ['name' => $grade['name']],
                [
                    'pangkat_name' => $grade['pangkat_name'],
                    'desc' => $grade['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
