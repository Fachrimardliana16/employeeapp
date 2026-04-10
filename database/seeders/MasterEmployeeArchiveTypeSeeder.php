<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeeArchiveType;
use App\Models\User;

class MasterEmployeeArchiveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $archives = [
            ['name' => 'KTP', 'desc' => 'Kartu Tanda Penduduk'],
            ['name' => 'IJAZAH', 'desc' => 'Ijazah Pendidikan'],
            ['name' => 'SERTIFIKAT', 'desc' => 'Sertifikat Keahlian/Profesi'],
        ];

        foreach ($archives as $archive) {
            MasterEmployeeArchiveType::firstOrCreate(
                ['name' => $archive['name']],
                [
                    'desc' => $archive['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
