<?php

namespace Database\Seeders;

use App\Models\JobApplication;
use App\Models\MasterEmployeePosition;
use App\Models\MasterDepartment;
use App\Models\MasterEmployeeEducation;
use App\Models\MasterSubDepartment;
use Illuminate\Database\Seeder;

class JobApplicationSampleSeeder extends Seeder
{
    public function run(): void
    {
        $positions = MasterEmployeePosition::all();
        $departments = MasterDepartment::all();
        $educations = MasterEmployeeEducation::all();
        $subDepartments = MasterSubDepartment::all();

        if ($positions->isEmpty() || $departments->isEmpty() || $educations->isEmpty()) {
            $this->command->error('Master data (positions, departments, or education) is missing. Please seed master data first.');
            return;
        }

        $applications = [
            [
                'name' => 'Ahmad Fauzi',
                'place_birth' => 'Jakarta',
                'date_birth' => '1995-05-15',
                'gender' => 'male',
                'marital_status' => 'single',
                'address' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'phone_number' => '081234567890',
                'email' => 'ahmad.fauzi@email.com',
                'id_number' => '3171051505950001',
                'education_institution' => 'Universitas Indonesia',
                'education_major' => 'Teknik Informatika',
                'education_graduation_year' => 2018,
                'education_gpa' => 3.65,
                'last_company_name' => 'PT. Tech Solutions',
                'last_position' => 'Junior Developer',
                'last_work_start_date' => '2018-07-01',
                'last_work_end_date' => '2024-12-31',
                'last_work_description' => 'Mengembangkan aplikasi web menggunakan Laravel dan Vue.js',
                'last_salary' => 6000000,
                'expected_salary' => 8000000,
                'available_start_date' => now()->addDays(30),
                'reference_name' => 'Budi Santoso',
                'reference_phone' => '081987654321',
                'reference_relation' => 'Supervisor',
                'status' => 'submitted',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'place_birth' => 'Bandung',
                'date_birth' => '1992-08-22',
                'gender' => 'female',
                'marital_status' => 'married',
                'address' => 'Jl. Gatot Subroto No. 456, Bandung',
                'phone_number' => '081987654321',
                'email' => 'siti.nurhaliza@email.com',
                'id_number' => '3273016208920002',
                'education_institution' => 'Institut Teknologi Bandung',
                'education_major' => 'Manajemen',
                'education_graduation_year' => 2015,
                'education_gpa' => 3.80,
                'last_company_name' => 'PT. ABC Indonesia',
                'last_position' => 'Marketing Executive',
                'last_work_start_date' => '2015-08-01',
                'last_work_end_date' => '2024-11-30',
                'last_work_description' => 'Mengelola strategi pemasaran dan hubungan dengan klien',
                'last_salary' => 7000000,
                'expected_salary' => 9000000,
                'available_start_date' => now()->addDays(14),
                'reference_name' => 'Indra Wijaya',
                'reference_phone' => '082123456789',
                'reference_relation' => 'Manager',
                'status' => 'reviewed',
            ],
            [
                'name' => 'Rizki Pratama',
                'place_birth' => 'Surabaya',
                'date_birth' => '1993-12-10',
                'gender' => 'male',
                'marital_status' => 'single',
                'address' => 'Jl. Pemuda No. 789, Surabaya',
                'phone_number' => '085666777888',
                'email' => 'rizki.pratama@email.com',
                'education_institution' => 'Universitas Airlangga',
                'education_major' => 'Akuntansi',
                'education_graduation_year' => 2016,
                'education_gpa' => 3.55,
                'last_company_name' => 'PT. Finance Corp',
                'last_position' => 'Staff Accounting',
                'last_work_start_date' => '2016-09-01',
                'last_work_end_date' => '2024-10-31',
                'last_work_description' => 'Menyiapkan laporan keuangan dan analisis budget',
                'last_salary' => 5500000,
                'expected_salary' => 7500000,
                'available_start_date' => now()->addDays(21),
                'status' => 'interview_scheduled',
                'interview_schedule' => [
                    'datetime' => now()->addDays(3)->setTime(10, 0),
                    'location' => 'Ruang Meeting A, Lantai 2',
                    'notes' => 'Interview untuk posisi Staff Accounting',
                    'scheduled_by' => 1,
                    'scheduled_at' => now(),
                ],
            ],
            [
                'name' => 'Maya Sari',
                'place_birth' => 'Medan',
                'date_birth' => '1994-03-18',
                'gender' => 'female',
                'marital_status' => 'single',
                'address' => 'Jl. Sisingamangaraja No. 321, Medan',
                'phone_number' => '081234567123',
                'email' => 'maya.sari@email.com',
                'education_institution' => 'Universitas Sumatera Utara',
                'education_major' => 'Psikologi',
                'education_graduation_year' => 2017,
                'education_gpa' => 3.72,
                'expected_salary' => 6500000,
                'available_start_date' => now()->addDays(45),
                'status' => 'interviewed',
                'interview_results' => [
                    'technical_score' => 85,
                    'soft_skills_score' => 90,
                    'overall_score' => 87,
                    'interviewer_notes' => 'Kandidat sangat baik dalam komunikasi dan memiliki pengalaman yang relevan',
                    'recommendation' => 'Direkomendasikan untuk diterima',
                ],
            ],
        ];

        foreach ($applications as $appData) {
            $position = $positions->random();
            $department = $departments->random();
            $education = $educations->random();
            $subDepartment = $subDepartments->where('departments_id', $department->id)->first();

            JobApplication::create(array_merge($appData, [
                'applied_position_id' => $position->id,
                'applied_department_id' => $department->id,
                'applied_sub_department_id' => $subDepartment?->id,
                'education_level_id' => $education->id,
            ]));
        }

        $this->command->info('Sample job applications created successfully!');
    }
}
