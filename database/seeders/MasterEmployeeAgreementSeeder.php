<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeeAgreement;
use App\Models\User;

class MasterEmployeeAgreementSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $agreements = [
            ['name' => 'PKWT (Perjanjian Kerja Waktu Tertentu)', 'desc' => 'Perjanjian kerja untuk pekerjaan sementara atau kontrak dengan jangka waktu tertentu (maksimal 5 tahun termasuk perpanjangan)'],
            ['name' => 'PKWTT (Perjanjian Kerja Waktu Tidak Tertentu)', 'desc' => 'Perjanjian kerja untuk pekerjaan tetap atau permanen tanpa batas waktu (berlaku hingga pensiun, resign, atau PHK)']
        ];

        foreach ($agreements as $agreement) {
            MasterEmployeeAgreement::updateOrCreate(
                ['name' => $agreement['name']],
                [
                    'desc' => $agreement['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
