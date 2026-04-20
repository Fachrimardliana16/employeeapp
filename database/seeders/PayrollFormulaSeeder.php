<?php
 
namespace Database\Seeders;
 
use App\Models\PayrollFormula;
use Illuminate\Database\Seeder;
 
class PayrollFormulaSeeder extends Seeder
{
    public function run(): void
    {
        $allComponents = [
            'TUN_KEL', 'TKK', 'TUN_JAB', 'TUN_KES', 'TUN_BERAS', 'TUN_AIR', 'ROUNDING',
            'POT_KOP_TAB', 'POT_KOP_DANSOS', 'POT_KOP_WAJIB', 'POT_AIR', 
            'POT_DAPENMA', 'POT_ASTEK', 'POT_BPJS_KES', 'POT_ABSEN', 'POT_LATE'
        ];
 
        $deductionsOnly = ['POT_ABSEN', 'POT_LATE'];
 
        $formulas = [
            [
                'formula_name' => 'Gaji Pegawai Tetap',
                'formula_code' => 'STD_PERM',
                'applies_to' => 'status',
                'applies_to_value' => 'Pegawai Tetap',
                'formula_components' => $allComponents,
                'percentage_multiplier' => 1.00,
                'description' => 'Gaji standar 100% dengan seluruh tunjangan dan potongan',
            ],
            [
                'formula_name' => 'Gaji Calon Pegawai',
                'formula_code' => 'STD_CPNS',
                'applies_to' => 'status',
                'applies_to_value' => 'Calon Pegawai',
                'formula_components' => $allComponents,
                'percentage_multiplier' => 0.80,
                'description' => 'Gaji 80% (Pokok & Tunjangan) untuk masa percobaan',
            ],
            [
                'formula_name' => 'Gaji Kontrak',
                'formula_code' => 'STD_KONTRAK',
                'applies_to' => 'status',
                'applies_to_value' => 'Kontrak',
                'formula_components' => $deductionsOnly,
                'percentage_multiplier' => 1.00,
                'description' => 'Gaji UMR tanpa tunjangan, hanya potongan kehadiran',
            ],
            [
                'formula_name' => 'Gaji THL',
                'formula_code' => 'STD_THL',
                'applies_to' => 'status',
                'applies_to_value' => 'Tenaga Harian Lepas',
                'formula_components' => $deductionsOnly,
                'percentage_multiplier' => 1.00,
                'description' => 'Gaji harian tanpa tunjangan, hanya potongan kehadiran',
            ],
            [
                'formula_name' => 'Gaji Magang',
                'formula_code' => 'STD_MAGANG',
                'applies_to' => 'status',
                'applies_to_value' => 'Magang',
                'formula_components' => $deductionsOnly,
                'percentage_multiplier' => 1.00,
                'description' => 'Gaji magang tanpa tunjangan',
            ],
        ];
 
        foreach ($formulas as $formula) {
            PayrollFormula::updateOrCreate(
                ['formula_code' => $formula['formula_code']],
                $formula
            );
        }
    }
}
