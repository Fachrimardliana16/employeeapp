<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\MasterEmployeePosition;
use App\Models\MasterEmployeeStatusEmployment;
use App\Models\User;

class SignatoryEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        // Dapatkan ID untuk posisi dan status yang diperlukan
        $direkturUtama = MasterEmployeePosition::where('name', 'Direktur Utama')->first();
        $direkturUmum = MasterEmployeePosition::where('name', 'Direktur Umum')->first();
        $direkturTeknik = MasterEmployeePosition::where('name', 'Direktur Teknik')->first();
        $direkturKeuangan = MasterEmployeePosition::where('name', 'Direktur Keuangan')->first();
        $kepalaBagian = MasterEmployeePosition::where('name', 'Kepala Bagian')->first();
        $statusTetap = MasterEmployeeStatusEmployment::where('name', 'Pegawai Tetap')->first();

        $signatoryEmployees = [
            [
                'nippam' => 'DIR001',
                'name' => 'Ir. Ahmad Setiawan, M.T.',
                'place_birth' => 'Jakarta',
                'date_birth' => '1970-05-15',
                'gender' => 'male',
                'religion' => 'Islam',
                'age' => 55,
                'address' => 'Jl. Merdeka No. 123, Purbalingga',
                'phone_number' => '081234567001',
                'id_number' => '3301010101700001',
                'email' => 'direktur.utama@tirtapewira.co.id',
                'entry_date' => '2015-01-01',
                'employee_position_id' => $direkturUtama?->id,
                'employment_status_id' => $statusTetap?->id,
                'users_id' => $user?->id,
            ],
            [
                'nippam' => 'DIR002',
                'name' => 'Drs. Bambang Wijaya, M.M.',
                'place_birth' => 'Semarang',
                'date_birth' => '1972-08-20',
                'gender' => 'male',
                'religion' => 'Islam',
                'age' => 53,
                'address' => 'Jl. Sudirman No. 456, Purbalingga',
                'phone_number' => '081234567002',
                'id_number' => '3301010101720002',
                'email' => 'direktur.umum@tirtapewira.co.id',
                'entry_date' => '2016-03-01',
                'employee_position_id' => $direkturUmum?->id,
                'employment_status_id' => $statusTetap?->id,
                'users_id' => $user?->id,
            ],
            [
                'nippam' => 'DIR003',
                'name' => 'Ir. Siti Nurhaliza, S.T.',
                'place_birth' => 'Yogyakarta',
                'date_birth' => '1975-12-10',
                'gender' => 'female',
                'religion' => 'Islam',
                'age' => 49,
                'address' => 'Jl. Gatot Subroto No. 789, Purbalingga',
                'phone_number' => '081234567003',
                'id_number' => '3301010101750003',
                'email' => 'direktur.teknik@tirtapewira.co.id',
                'entry_date' => '2017-06-01',
                'employee_position_id' => $direkturTeknik?->id,
                'employment_status_id' => $statusTetap?->id,
                'users_id' => $user?->id,
            ],
            [
                'nippam' => 'DIR004',
                'name' => 'Dra. Indah Permatasari, M.Ak.',
                'place_birth' => 'Solo',
                'date_birth' => '1973-04-25',
                'gender' => 'female',
                'religion' => 'Islam',
                'age' => 52,
                'address' => 'Jl. Ahmad Yani No. 321, Purbalingga',
                'phone_number' => '081234567004',
                'id_number' => '3301010101730004',
                'email' => 'direktur.keuangan@tirtapewira.co.id',
                'entry_date' => '2018-01-15',
                'employee_position_id' => $direkturKeuangan?->id,
                'employment_status_id' => $statusTetap?->id,
                'users_id' => $user?->id,
            ],
            [
                'nippam' => 'KBG001',
                'name' => 'Drs. Joko Susilo, M.Si.',
                'place_birth' => 'Cilacap',
                'date_birth' => '1976-09-30',
                'gender' => 'male',
                'religion' => 'Islam',
                'age' => 48,
                'address' => 'Jl. Diponegoro No. 654, Purbalingga',
                'phone_number' => '081234567005',
                'id_number' => '3301010101760005',
                'email' => 'kepala.bagian.umum@tirtapewira.co.id',
                'entry_date' => '2019-04-01',
                'employee_position_id' => $kepalaBagian?->id,
                'employment_status_id' => $statusTetap?->id,
                'users_id' => $user?->id,
            ],
        ];

        foreach ($signatoryEmployees as $employeeData) {
            Employee::firstOrCreate(
                ['nippam' => $employeeData['nippam']],
                $employeeData
            );
        }

        $this->command->info('Signatory employees seeded successfully!');
        $this->command->info('Created employees with signatory positions (Directors and Department Heads)');
    }
}
