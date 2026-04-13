<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RoleSeeder::class,
            MasterDepartmentSeeder::class,
            MasterSubDepartmentSeeder::class,
            MasterEmployeePositionSeeder::class,
            MasterEmployeeStatusEmploymentSeeder::class,
            MasterEmployeeAgreementSeeder::class,
            MasterEmployeeArchiveTypeSeeder::class,
            MasterEmployeeBenefitSeeder::class,
            MasterEmployeeEducationSeeder::class,
            MasterEmployeeFamilySeeder::class,
            MasterEmployeePermissionSeeder::class,
            MasterOfficeLocationSeeder::class,
            AttendanceScheduleSeeder::class,
            PnsSalarySeeder::class,
            MasterEmployeeNonPermanentSalarySeeder::class,
            EmployeeDataSeeder::class,
        ]);
    }
}
