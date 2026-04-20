<?php

namespace Database\Seeders;

use App\Models\PayrollComponent;
use Illuminate\Database\Seeder;

class PayrollComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            // INCOMES
            [
                'component_name' => 'Tunjangan Keluarga',
                'component_code' => 'TUN_KEL',
                'component_type' => 'income',
                'calculation_method' => 'formula',
                'formula' => '{base_salary} * 0.10',
                'default_amount' => 0,
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'Tunjangan Istri/Suami (10%)',
            ],
            [
                'component_name' => 'TKK (Tunjangan Kesejahteraan Karyawan)',
                'component_code' => 'TKK',
                'component_type' => 'income',
                'calculation_method' => 'fixed',
                'default_amount' => 1200000,
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'Tunjangan Kesejahteraan Karyawan',
            ],
            [
                'component_name' => 'Tunjangan Jabatan',
                'component_code' => 'TUN_JAB',
                'component_type' => 'income',
                'calculation_method' => 'fixed',
                'default_amount' => 600000,
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'Tunjangan Jabatan Struktural',
            ],
            [
                'component_name' => 'Tunjangan BPJS Kesehatan (Kantor)',
                'component_code' => 'TUN_BPJS_KES_KTR',
                'component_type' => 'income',
                'calculation_method' => 'formula',
                'formula' => '{base_salary} * 0.04',
                'default_amount' => 0,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Kontribusi BPJS Kesehatan dari Perusahaan (4%)',
            ],
            [
                'component_name' => 'Tunjangan JHT (Kantor)',
                'component_code' => 'TUN_JHT_KTR',
                'component_type' => 'income',
                'calculation_method' => 'formula',
                'formula' => '{base_salary} * 0.037',
                'default_amount' => 0,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Kontribusi JHT dari Perusahaan (3.7%)',
            ],
            [
                'component_name' => 'Tunjangan Beras',
                'component_code' => 'TUN_BERAS',
                'component_type' => 'income',
                'calculation_method' => 'fixed',
                'default_amount' => 150000,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Tunjangan Beras Pegawai',
            ],
            [
                'component_name' => 'Tunjangan Air',
                'component_code' => 'TUN_AIR',
                'component_type' => 'income',
                'calculation_method' => 'fixed',
                'default_amount' => 101100,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Subsidi Penggunaan Air PDAM',
            ],
            [
                'component_name' => 'Pembulatan',
                'component_code' => 'ROUNDING',
                'component_type' => 'income',
                'calculation_method' => 'fixed',
                'default_amount' => 50,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Pembulatan Gaji',
            ],

            // DEDUCTIONS
            [
                'component_name' => 'Iuran Kesehatan BPJS (Pegawai)',
                'component_code' => 'POT_BPJS_KES_PEG',
                'component_type' => 'deduction',
                'calculation_method' => 'formula',
                'formula' => '{base_salary} * 0.01',
                'default_amount' => 0,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Iuran BPJS Kesehatan dari Pegawai (1%)',
            ],
            [
                'component_name' => 'Iuran JHT (Pegawai)',
                'component_code' => 'POT_JHT_PEG',
                'component_type' => 'deduction',
                'calculation_method' => 'formula',
                'formula' => '{base_salary} * 0.02',
                'default_amount' => 0,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Iuran JHT dari Pegawai (2%)',
            ],
            [
                'component_name' => 'IWP (Iuran Wajib Pegawai)',
                'component_code' => 'POT_IWP',
                'component_type' => 'deduction',
                'calculation_method' => 'formula',
                'formula' => '{base_salary} * 0.08',
                'default_amount' => 0,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Iuran Wajib Pegawai (Pensiun/TASPEN)',
            ],
            [
                'component_name' => 'DAPENMA',
                'component_code' => 'POT_DAPENMA',
                'component_type' => 'deduction',
                'calculation_method' => 'formula',
                'formula' => '{base_salary} * 0.105', 
                'default_amount' => 0,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Dana Pensiun Pegawai',
            ],
            [
                'component_name' => 'Tabungan Koperasi',
                'component_code' => 'POT_KOP_TAB',
                'component_type' => 'deduction',
                'calculation_method' => 'fixed',
                'default_amount' => 100000,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Tabungan Koperasi Pegawai',
            ],
            [
                'component_name' => 'Iuran Dansos Koperasi',
                'component_code' => 'POT_KOP_DANSOS',
                'component_type' => 'deduction',
                'calculation_method' => 'fixed',
                'default_amount' => 6500,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Iuran Dana Sosial Koperasi',
            ],
            [
                'component_name' => 'Simpanan Wajib Koperasi',
                'component_code' => 'POT_KOP_WAJIB',
                'component_type' => 'deduction',
                'calculation_method' => 'fixed',
                'default_amount' => 35000,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Simpanan Wajib Koperasi',
            ],
            [
                'component_name' => 'Rekening Air Minum',
                'component_code' => 'POT_AIR',
                'component_type' => 'deduction',
                'calculation_method' => 'fixed',
                'default_amount' => 62190,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Tagihan Rekening Air Pegawai',
            ],
            [
                'component_name' => 'Potongan Absen',
                'component_code' => 'POT_ABSEN',
                'component_type' => 'deduction',
                'calculation_method' => 'formula',
                'formula' => '({base_salary} / 30) * {absent_count}',
                'default_amount' => 0,
                'is_taxable' => false,
                'is_active' => true,
                'description' => 'Potongan karena tidak hadir',
            ],
        ];

        foreach ($components as $component) {
            PayrollComponent::updateOrCreate(
                ['component_code' => $component['component_code']],
                $component
            );
        }
    }
}
