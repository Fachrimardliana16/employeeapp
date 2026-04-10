<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeePosition;
use App\Models\User;

class MasterEmployeePositionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $positions = [
            ['name' => 'Staff', 'desc' => 'Posisi Staff'],
            ['name' => 'Koordinator Lapangan', 'desc' => 'Posisi Koordinator Lapangan'],
            ['name' => 'Kepala Seksi Teknik', 'desc' => 'Posisi Kepala Cabang'],
            ['name' => 'Kepala Seksi Umum', 'desc' => 'Posisi Kepala Unit'],
            ['name' => 'Kepala Cabang', 'desc' => 'Posisi Kepala Cabang'],
            ['name' => 'Kepala Unit', 'desc' => 'Posisi Kepala Unit'],
            ['name' => 'Kepala Sub Bagian', 'desc' => 'Posisi Kepala Sub Bagian'],
            ['name' => 'Kepala Bagian', 'desc' => 'Posisi Kepala Bagian'],
            ['name' => 'Direktur Keuangan', 'desc' => 'Posisi Direktur Keuangan'],
            ['name' => 'Direktur Umum', 'desc' => 'Posisi Direktur Umum'],
            ['name' => 'Direktur Teknik', 'desc' => 'Posisi Direktur Teknik'],
            ['name' => 'Direktur Utama', 'desc' => 'Posisi Direktur Utama'],
        ];

        foreach ($positions as $position) {
            MasterEmployeePosition::updateOrCreate(
                ['name' => $position['name']],
                [
                    'desc' => $position['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
