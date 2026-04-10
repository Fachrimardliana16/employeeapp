<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterDepartment;
use App\Models\User;

class MasterDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $departments = [
            ['name' => 'Bagian Umum', 'key' => 'BGN-UMUM', 'type' => 'Bagian'],
            ['name' => 'Bagian Keuangan', 'key' => 'BGN-KEU', 'type' => 'Bagian'],
            ['name' => 'Bagian Teknik', 'key' => 'BGN-TEKNIK', 'type' => 'Bagian'],
            ['name' => 'Bagian Hubungan Langganan', 'key' => 'BGN-HUBLAN', 'type' => 'Bagian'],
            ['name' => 'Satuan Pengawasan Intern (SPI)', 'key' => 'SPI', 'type' => 'Bagian'],
            
            ['name' => 'Cabang Kota Bangga', 'key' => 'CBG-KB', 'type' => 'Cabang'],
            ['name' => 'Cabang Jendral Soedirman', 'key' => 'CBG-JS', 'type' => 'Cabang'],
            ['name' => 'Cabang Usman Janatin', 'key' => 'CBG-UJ', 'type' => 'Cabang'],
            ['name' => 'Cabang Ardilawet', 'key' => 'CBG-AL', 'type' => 'Cabang'],
            ['name' => 'Cabang Goentoer Djarjono', 'key' => 'CBG-GD', 'type' => 'Cabang'],
            
            ['name' => 'Unit IKK Kemangkon', 'key' => 'UNT-KMK', 'type' => 'Unit'],
            ['name' => 'Unit IKK Bukateja', 'key' => 'UNT-BKT', 'type' => 'Unit'],
            ['name' => 'Unit IKK Karangreja', 'key' => 'UNT-KRJ', 'type' => 'Unit'],
            ['name' => 'Unit IKK Rembang', 'key' => 'UNT-RMG', 'type' => 'Unit'],
        ];

        foreach ($departments as $dept) {
            MasterDepartment::updateOrCreate(
                ['name' => $dept['name']],
                [
                    'type' => $dept['type'] ?? 'Bagian',
                    'desc' => $dept['name'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
