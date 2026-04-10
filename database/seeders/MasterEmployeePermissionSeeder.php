<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeePermission;
use App\Models\User;

class MasterEmployeePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $permissions = [
            ['name' => 'Cuti Sakit', 'desc' => 'Cuti karena sakit dengan surat keterangan dokter'],
            ['name' => 'Cuti Haid', 'desc' => 'Cuti bagi karyawan perempuan yang merasakan sakit saat haid'],
            ['name' => 'Cuti Alasan Penting/Keperluan Keluarga', 'desc' => 'Cuti untuk keperluan keluarga mendesak'],
            ['name' => 'Cuti Tahunan', 'desc' => 'Cuti tahunan minimal 12 hari kerja'],
            ['name' => 'Cuti Melahirkan', 'desc' => 'Cuti bagi karyawan perempuan: 1.5 bulan sebelum dan sesudah'],
            ['name' => 'Cuti Keguguran', 'desc' => 'Cuti bagi karyawan perempuan karena keguguran'],
            ['name' => 'Cuti Menikah', 'desc' => 'Cuti karena pernikahan sendiri: 3 hari'],
            ['name' => 'Cuti Menikahkan Anak', 'desc' => 'Cuti menghadiri pernikahan anak: 2 hari'],
            ['name' => 'Cuti Khitanan atau Baptis Anak', 'desc' => 'Cuti menghadiri khitanan atau baptis anak: 2 hari'],
            ['name' => 'Cuti Mendampingi Istri Melahirkan atau Keguguran', 'desc' => 'Cuti bagi karyawan laki-laki'],
            ['name' => 'Cuti Kematian Keluarga Inti', 'desc' => 'Meninggalnya orang tua, mertua, pasangan, atau anak'],
            ['name' => 'Cuti Kematian Keluarga Serumah Lainnya', 'desc' => 'Meninggalnya anggota keluarga serumah (bukan inti)'],
            ['name' => 'Cuti Haji atau Umrah', 'desc' => 'Cuti menjalankan ibadah haji/umrah'],
            ['name' => 'Cuti Besar (Istirahat Panjang)', 'desc' => 'Cuti besar untuk karyawan dengan masa kerja >=6 tahun'],
            ['name' => 'Cuti Tanpa Bayar (Diluar Tanggungan)', 'desc' => 'Cuti di luar tanggungan perusahaan']
        ];

        foreach ($permissions as $permission) {
            MasterEmployeePermission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'desc' => $permission['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
