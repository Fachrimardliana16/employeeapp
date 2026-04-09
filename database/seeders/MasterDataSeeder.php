<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterDepartment;
use App\Models\MasterSubDepartment;
use App\Models\MasterEmployeeAgreement;
use App\Models\MasterEmployeeArchiveType;
use App\Models\MasterEmployeeBenefit;
use App\Models\MasterEmployeeEducation;
use App\Models\MasterEmployeeFamily;
use App\Models\MasterEmployeeGrade;
use App\Models\MasterEmployeeBasicSalary;
use App\Models\MasterEmployeeServiceGrade;
use App\Models\MasterEmployeePermission;
use App\Models\MasterEmployeePosition;
use App\Models\MasterEmployeeStatusEmployment;
use App\Models\User;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create([
            'name' => 'Admin System',
            'email' => 'admin@employapp.com',
        ]);

        $this->createDepartments($user);
        $this->createSubDepartments($user);
        $this->createAgreementTypes($user);
        $this->createArchiveTypes($user);
        $this->createBenefitTypes($user);
        $this->createEducationLevels($user);
        $this->createFamilyRelations($user);
        $this->createEmployeeGrades($user);
        $this->createServiceGrades($user);
        $this->createPermissionTypes($user);
        $this->createPositions($user);
        $this->createEmploymentStatus($user);
        $this->createBasicSalaries($user);

        $this->command->info('Master data seeded successfully!');
    }

    private function createDepartments($user)
    {
        $departments = [
            ['name' => 'Bagian Umum', 'key' => 'BGN-UMUM'],
            ['name' => 'Bagian Keuangan', 'key' => 'BGN-KEU'],
            ['name' => 'Bagian Teknik', 'key' => 'BGN-TEKNIK'],
            ['name' => 'Bagian Hubungan Langganan', 'key' => 'BGN-HUBLAN'],
            ['name' => 'Satuan Pengawasan Intern (SPI)', 'key' => 'SPI'],
        ];

        foreach ($departments as $dept) {
            MasterDepartment::firstOrCreate(
                ['name' => $dept['name']],
                [
                    'desc' => $dept['name'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createSubDepartments($user)
    {
        $subDepartments = [
            // Sub Bagian Umum
            ['name' => 'Teknologi Informasi', 'parent' => 'Bagian Umum'],
            ['name' => 'Kerumahtanggaan', 'parent' => 'Bagian Umum'],
            ['name' => 'Kepegawaian', 'parent' => 'Bagian Umum'],
            ['name' => 'Kesekretariatan', 'parent' => 'Bagian Umum'],

            // Sub Bagian Keuangan
            ['name' => 'Anggaran dan Pendapatan', 'parent' => 'Bagian Keuangan'],
            ['name' => 'Verifikasi Pembukuan', 'parent' => 'Bagian Keuangan'],
            ['name' => 'Gudang', 'parent' => 'Bagian Keuangan'],

            // Sub Bagian Teknik
            ['name' => 'NRW dan GIS', 'parent' => 'Bagian Teknik'],
            ['name' => 'Perencanaan', 'parent' => 'Bagian Teknik'],
            ['name' => 'Produksi', 'parent' => 'Bagian Teknik'],
            ['name' => 'Transmisi dan Distribusi', 'parent' => 'Bagian Teknik'],

            // Sub Bagian Hubungan Langganan
            ['name' => 'Pemasaran', 'parent' => 'Bagian Hubungan Langganan'],
            ['name' => 'Layanan Langganan', 'parent' => 'Bagian Hubungan Langganan'],
            ['name' => 'Baca Meter', 'parent' => 'Bagian Hubungan Langganan'],

            // Sub Bagian Umum tambahan
            ['name' => 'Hukum dan Humas', 'parent' => 'Bagian Umum'],
        ];

        foreach ($subDepartments as $subDept) {
            $parentDept = MasterDepartment::where('name', $subDept['parent'])->first();
            if ($parentDept) {
                MasterSubDepartment::firstOrCreate(
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

    private function createAgreementTypes($user)
    {
        $agreements = [
            ['name' => 'PKWT (Perjanjian Kerja Waktu Tertentu)', 'desc' => 'Perjanjian kerja untuk pekerjaan sementara atau kontrak dengan jangka waktu tertentu (maksimal 5 tahun termasuk perpanjangan); wajib dibuat tertulis dalam bahasa Indonesia dan huruf latin, dilaporkan ke Disnaker dalam 7 hari; tidak boleh untuk pekerjaan tetap; hak pekerja termasuk upah minimum, THR (jika kerja ≥1 bulan), cuti tahunan (jika ≥12 bulan), BPJS Ketenagakerjaan/Kesehatan, dan uang kompensasi saat berakhir (proporsional, misalnya 1 bulan upah untuk 12 bulan kerja, Pasal 16 PP 35/2021); perpanjangan boleh jika pekerjaan belum selesai, tapi total tidak >5 tahun; tidak boleh ada masa percobaan (Pasal 81 ayat 12 UU Ketenagakerjaan jo. Pasal 8 PP 35/2021). Jika melebihi batas atau tidak tertulis, batal demi hukum menjadi PKWTT.'],
            ['name' => 'PKWTT (Perjanjian Kerja Waktu Tidak Tertentu)', 'desc' => 'Perjanjian kerja untuk pekerjaan tetap atau permanen tanpa batas waktu (berlaku hingga pensiun, resign, atau PHK); boleh dibuat tertulis atau lisan, tapi wajib ada surat pengangkatan jika lisan (Pasal 63 UU Ketenagakerjaan); boleh ada masa percobaan maksimal 3 bulan dengan upah minimum; hak pekerja termasuk upah minimum, THR penuh, cuti tahunan 12 hari setelah 12 bulan kerja, BPJS Ketenagakerjaan/Kesehatan, dan pesangon/penggantian hak jika PHK (Pasal 156 UU Ketenagakerjaan); PHK harus melalui prosedur adil dan LPPHI jika berselisih; masa kerja PKWT sebelumnya dihitung untuk hak ini (Pasal 2 ayat 2 PP 35/2021).']
        ];

        foreach ($agreements as $agreement) {
            MasterEmployeeAgreement::firstOrCreate(
                ['name' => $agreement['name']],
                [
                    'desc' => $agreement['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createArchiveTypes($user)
    {
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

    private function createBenefitTypes($user)
    {
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
            MasterEmployeeBenefit::firstOrCreate(
                ['name' => $benefit['name']],
                [
                    'desc' => $benefit['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createEducationLevels($user)
    {
        $educations = [
            ['name' => 'SD', 'desc' => 'Sekolah Dasar'],
            ['name' => 'SMP', 'desc' => 'Sekolah Menengah Pertama'],
            ['name' => 'SMA', 'desc' => 'Sekolah Menengah Atas'],
            ['name' => 'D1', 'desc' => 'Diploma 1'],
            ['name' => 'D3', 'desc' => 'Diploma 3'],
            ['name' => 'D4', 'desc' => 'Diploma 4'],
            ['name' => 'S1', 'desc' => 'Sarjana Strata 1'],
            ['name' => 'S2', 'desc' => 'Sarjana Strata 2 (Magister)'],
            ['name' => 'S3', 'desc' => 'Sarjana Strata 3 (Doktor)'],
        ];

        foreach ($educations as $education) {
            MasterEmployeeEducation::firstOrCreate(
                ['name' => $education['name']],
                [
                    'desc' => $education['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createFamilyRelations($user)
    {
        $relations = [
            ['name' => 'Suami', 'desc' => 'Hubungan sebagai suami'],
            ['name' => 'Istri', 'desc' => 'Hubungan sebagai istri'],
            ['name' => 'Orang Tua', 'desc' => 'Hubungan sebagai orang tua'],
            ['name' => 'Anak', 'desc' => 'Hubungan sebagai anak'],
        ];

        foreach ($relations as $relation) {
            MasterEmployeeFamily::firstOrCreate(
                ['name' => $relation['name']],
                [
                    'desc' => $relation['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createEmployeeGrades($user)
    {
        $grades = [
            'A1', 'A2', 'A3', 'A4',
            'B1', 'B2', 'B3', 'B4',
            'C1', 'C2', 'C3', 'C4',
            'D1', 'D2', 'D3', 'D4'
        ];

        foreach ($grades as $grade) {
            MasterEmployeeGrade::firstOrCreate(
                ['name' => $grade],
                [
                    'desc' => 'Golongan ' . $grade,
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createPermissionTypes($user)
    {
        $permissions = [
            ['name' => 'Cuti Sakit', 'desc' => 'Cuti karena sakit dengan surat keterangan dokter; upah 100% untuk 4 bulan pertama, 75% untuk 4 bulan kedua, 50% untuk 4 bulan ketiga, dan 25% untuk bulan berikutnya (Pasal 93 ayat 3). Tidak terbatas hari jika ada bukti medis.'],
            ['name' => 'Cuti Haid', 'desc' => 'Cuti bagi karyawan perempuan yang merasakan sakit saat haid; maksimal 2 hari (hari pertama dan kedua) per bulan, upah penuh (Pasal 81).'],
            ['name' => 'Cuti Alasan Penting/Keperluan Keluarga', 'desc' => 'Cuti untuk keperluan keluarga mendesak seperti mendampingi sakit keluarga; durasi sesuai kesepakatan, upah penuh jika sesuai alasan spesifik (Pasal 93 ayat 2). Bisa dipotong dari cuti tahunan jika tanpa bukti.'],
            ['name' => 'Cuti Tahunan', 'desc' => 'Cuti tahunan minimal 12 hari kerja setelah 12 bulan bekerja terus-menerus; upah penuh. Pelaksanaan diatur dalam perjanjian kerja atau peraturan perusahaan (Pasal 79 ayat 3).'],
            ['name' => 'Cuti Melahirkan', 'desc' => 'Cuti bagi karyawan perempuan: 1,5 bulan sebelum dan 1,5 bulan setelah melahirkan (total 3 bulan), atau sesuai dokter/bidan; upah penuh (Pasal 82 ayat 1). Bisa diperpanjang hingga 6 bulan sesuai UU KIA 2023 untuk ibu menyusui.'],
            ['name' => 'Cuti Keguguran', 'desc' => 'Cuti bagi karyawan perempuan karena keguguran: 1,5 bulan atau sesuai surat dokter/bidan; upah penuh (Pasal 82 ayat 2).'],
            ['name' => 'Cuti Menikah', 'desc' => 'Cuti karena pernikahan sendiri: 3 hari; upah penuh (Pasal 93 ayat 4).'],
            ['name' => 'Cuti Menikahkan Anak', 'desc' => 'Cuti menghadiri pernikahan anak: 2 hari; upah penuh (Pasal 93 ayat 4).'],
            ['name' => 'Cuti Khitanan atau Baptis Anak', 'desc' => 'Cuti menghadiri khitanan atau baptis anak: 2 hari; upah penuh (Pasal 93 ayat 4). Untuk saudara, gunakan cuti alasan penting.'],
            ['name' => 'Cuti Mendampingi Istri Melahirkan atau Keguguran', 'desc' => 'Cuti bagi karyawan laki-laki mendampingi istri: 2 hari; upah penuh (Pasal 93 ayat 4).'],
            ['name' => 'Cuti Kematian Keluarga Inti', 'desc' => 'Cuti karena meninggalnya orang tua, mertua, suami/istri, atau anak/menantu: 2 hari; upah penuh (Pasal 93 ayat 4).'],
            ['name' => 'Cuti Kematian Keluarga Serumah Lainnya', 'desc' => 'Cuti karena meninggalnya anggota keluarga serumah (bukan inti): 1 hari; upah penuh (Pasal 93 ayat 4).'],
            ['name' => 'Cuti Haji atau Umrah', 'desc' => 'Cuti menjalankan ibadah haji/umrah: durasi sesuai kebutuhan (satu kali seumur hidup); upah penuh (Pasal 93 ayat 2).'],
            ['name' => 'Cuti Besar (Istirahat Panjang)', 'desc' => 'Cuti besar untuk karyawan dengan masa kerja ≥6 tahun di perusahaan tertentu: minimal 2 bulan (1 bulan di tahun ke-7 dan 1 bulan di tahun ke-8); tidak berhak cuti tahunan selama periode ini. Opsional sesuai perjanjian kerja (Pasal 79 ayat 5).'],
            ['name' => 'Cuti Tanpa Bayar (Diluar Tanggungan)', 'desc' => 'Cuti di luar tanggungan perusahaan untuk keperluan pribadi (misalnya studi atau dinas pasangan): durasi sesuai kesepakatan; upah tidak dibayar (Pasal 93 ayat 1).']
        ];

        foreach ($permissions as $permission) {
            MasterEmployeePermission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'desc' => $permission['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createPositions($user)
    {
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
            MasterEmployeePosition::firstOrCreate(
                ['name' => $position['name']],
                [
                    'desc' => $position['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createEmploymentStatus($user)
    {
        $statuses = [
            ['name' => 'Tenaga Harian Lepas', 'desc' => 'Pegawai harian lepas'],
            ['name' => 'Magang', 'desc' => 'Magang'],
            ['name' => 'Kontrak', 'desc' => 'Pegawai kontrak'],
            ['name' => 'Calon Pegawai', 'desc' => 'Calon pegawai tetap'],
            ['name' => 'Pegawai Tetap', 'desc' => 'Pegawai tetap'],
            ['name' => 'Pensiun', 'desc' => 'Purna Tugas'],
        ];

        foreach ($statuses as $status) {
            MasterEmployeeStatusEmployment::firstOrCreate(
                ['name' => $status['name']],
                [
                    'desc' => $status['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createServiceGrades($user)
    {
        // Get first employee grade to associate with service grades
        $employeeGrade = MasterEmployeeGrade::first();

        if (!$employeeGrade) {
            $this->command->warn('No employee grades found. Service grades will be skipped.');
            return;
        }

        $serviceGrades = [
            ['service_grade' => 'Masa Kerja 0-5 Tahun', 'desc' => 'Masa kerja awal'],
            ['service_grade' => 'Masa Kerja 6-10 Tahun', 'desc' => 'Masa kerja menengah'],
            ['service_grade' => 'Masa Kerja 11-20 Tahun', 'desc' => 'Masa kerja senior'],
            ['service_grade' => 'Masa Kerja >20 Tahun', 'desc' => 'Masa kerja veteran'],
        ];

        foreach ($serviceGrades as $serviceGrade) {
            MasterEmployeeServiceGrade::firstOrCreate(
                ['service_grade' => $serviceGrade['service_grade']],
                [
                    'employee_grade_id' => $employeeGrade->id,
                    'desc' => $serviceGrade['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }

    private function createBasicSalaries($user)
    {
        // UMR Baseline (e.g. for Kontrak or Entry Level)
        $umrValue = 2500000;

        $salaries = [
            'A1' => 1000000,
            'A2' => 1200000,
            'A3' => 1500000,
            'A4' => 1800000,
            'B1' => 2200000,
            'B2' => 2600000,
            'B3' => 3000000,
            'B4' => 3500000,
            'C1' => 4000000,
            'C2' => 4500000,
            'C3' => 5000000,
            'C4' => 5500000,
            'D1' => 6000000,
            'D2' => 7000000,
            'D3' => 8000000,
            'D4' => 10000000,
        ];

        $serviceGrade = MasterEmployeeServiceGrade::first();

        foreach ($salaries as $gradeName => $amount) {
            $grade = MasterEmployeeGrade::where('name', $gradeName)->first();
            if ($grade && $serviceGrade) {
                MasterEmployeeBasicSalary::updateOrCreate(
                    [
                        'employee_grade_id' => $grade->id,
                        'employee_service_grade_id' => $serviceGrade->id,
                    ],
                    [
                        'amount' => $amount,
                        'desc' => 'Gaji pokok untuk golongan ' . $gradeName,
                        'is_active' => true,
                        'users_id' => $user->id,
                    ]
                );

                // Sync to MasterEmployeeGrade.basic_salary if field exists for easy access
                $grade->update(['basic_salary' => $amount]);
            }
        }
    }
}
