<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterDepartment;
use App\Models\MasterSubDepartment;
use App\Models\User;

class MasterSubDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $subDepartments = [
            ['name' => 'Teknologi Informasi', 'parent' => 'Bagian Umum'],
            ['name' => 'Kerumahtanggaan', 'parent' => 'Bagian Umum'],
            ['name' => 'Kepegawaian', 'parent' => 'Bagian Umum'],
            ['name' => 'Kesekretariatan', 'parent' => 'Bagian Umum'],
            ['name' => 'Anggaran dan Pendapatan', 'parent' => 'Bagian Keuangan'],
            ['name' => 'Verifikasi Pembukuan', 'parent' => 'Bagian Keuangan'],
            ['name' => 'Gudang', 'parent' => 'Bagian Keuangan'],
            ['name' => 'NRW dan GIS', 'parent' => 'Bagian Teknik'],
            ['name' => 'Perencanaan', 'parent' => 'Bagian Teknik'],
            ['name' => 'Produksi', 'parent' => 'Bagian Teknik'],
            ['name' => 'Transmisi dan Distribusi', 'parent' => 'Bagian Teknik'],
            ['name' => 'Pemasaran', 'parent' => 'Bagian Hubungan Langganan'],
            ['name' => 'Layanan Langganan', 'parent' => 'Bagian Hubungan Langganan'],
            ['name' => 'Baca Meter', 'parent' => 'Bagian Hubungan Langganan'],
            ['name' => 'Hukum dan Humas', 'parent' => 'Bagian Umum'],
        ];

        foreach ($subDepartments as $subDept) {
            $parentDept = MasterDepartment::where('name', $subDept['parent'])->first();
            if ($parentDept) {
                MasterSubDepartment::updateOrCreate(
                    ['name' => $subDept['name']],
                    [
                        'departments_id' => $parentDept->id,
                        'desc' => $subDept['name'],
                        'is_active' => true,
                        'users_id' => $user->id,
                    ]
                );
            }
        }
    }
}
