<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeePosition;
use App\Models\MasterEmployeeBenefit;
use App\Models\MasterEmployeePositionBenefit;
use App\Models\MasterEmployeeSalaryCut;
use App\Models\MasterEmployeePositionSalaryCut;
use App\Models\User;

class PositionPayrollSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        
        $benefits = MasterEmployeeBenefit::pluck('id', 'name')->toArray();
        $positions = MasterEmployeePosition::pluck('id', 'name')->toArray();

        // Seed Master Salary Cuts first
        $cutNames = [
            'Iuran Dansoskop' => 'Iuran Dana Sosial Koperasi',
            'ASTEK' => 'BPJS Ketenagakerjaan (Bagian Pegawai)',
            'DAPENMA' => 'Dana Pensiun Pegawai',
        ];

        foreach ($cutNames as $name => $desc) {
            MasterEmployeeSalaryCut::updateOrCreate(
                ['name' => $name],
                ['desc' => $desc, 'is_active' => true, 'users_id' => $user->id]
            );
        }

        $cuts = MasterEmployeeSalaryCut::pluck('id', 'name')->toArray();

        $tiers = [
            'Staff' => [
                'positions' => ['Staff'],
                'benefits' => [
                    'Tunjangan Jabatan' => 600000,
                    'TKK' => 1200000,
                    'Tunjangan Beras' => 150000,
                    'Tunjangan Air' => 101100,
                ],
                'cuts' => [
                    'Iuran Dansoskop' => 6500,
                    'ASTEK' => 74242,
                    'DAPENMA' => 214732,
                ]
            ],
            'Level 2 (Koor/Seksi)' => [
                'positions' => ['Koordinator Lapangan', 'Kepala Seksi Teknik', 'Kepala Seksi Umum'],
                'benefits' => [
                    'Tunjangan Jabatan' => 1200000,
                    'TKK' => 2200000,
                    'Tunjangan Beras' => 160000,
                    'Tunjangan Air' => 101100,
                ],
                'cuts' => [
                    'Iuran Dansoskop' => 10000,
                    'ASTEK' => 150000,
                    'DAPENMA' => 450000,
                ]
            ],
            'Level 3 (Unit/Subag)' => [
                'positions' => ['Kepala Unit', 'Kepala Sub Bagian'],
                'benefits' => [
                    'Tunjangan Jabatan' => 1800000,
                    'TKK' => 3500000,
                    'Tunjangan Beras' => 180000,
                    'Tunjangan Air' => 101100,
                ],
                'cuts' => [
                    'Iuran Dansoskop' => 15000,
                    'ASTEK' => 250000,
                    'DAPENMA' => 700000,
                ]
            ],
            'Level 4 (Bagian/Cabang)' => [
                'positions' => ['Kepala Bagian', 'Kepala Cabang'],
                'benefits' => [
                    'Tunjangan Jabatan' => 2500000,
                    'TKK' => 6000000,
                    'Tunjangan Beras' => 200000,
                    'Tunjangan Air' => 101100,
                ],
                'cuts' => [
                    'Iuran Dansoskop' => 25000,
                    'ASTEK' => 400000,
                    'DAPENMA' => 1000000,
                ]
            ],
            'Level 5 (Direksi)' => [
                'positions' => ['Direktur Keuangan', 'Direktur Umum', 'Direktur Teknik'],
                'benefits' => [
                    'Tunjangan Jabatan' => 4500000,
                    'TKK' => 11000000,
                    'Tunjangan Beras' => 220000,
                    'Tunjangan Air' => 101100,
                ],
                'cuts' => [
                    'Iuran Dansoskop' => 40000,
                    'ASTEK' => 600000,
                    'DAPENMA' => 1500000,
                ]
            ],
            'Level 6 (Top)' => [
                'positions' => ['Direktur Utama'],
                'benefits' => [
                    'Tunjangan Jabatan' => 6500000,
                    'TKK' => 15000000,
                    'Tunjangan Beras' => 250000,
                    'Tunjangan Air' => 101100,
                ],
                'cuts' => [
                    'Iuran Dansoskop' => 50000,
                    'ASTEK' => 800000,
                    'DAPENMA' => 2000000,
                ]
            ],
        ];

        foreach ($tiers as $tierName => $data) {
            foreach ($data['positions'] as $positionName) {
                if (isset($positions[$positionName])) {
                    $posId = $positions[$positionName];
                    $this->seedBenefits($posId, $data['benefits'], $benefits, $user->id);
                    $this->seedCuts($posId, $data['cuts'], $cuts, $user->id);
                }
            }
        }
    }

    private function seedBenefits(int $positionId, array $values, array $benefitIds, int $userId)
    {
        foreach ($values as $benefitName => $amount) {
            if (isset($benefitIds[$benefitName])) {
                MasterEmployeePositionBenefit::updateOrCreate(
                    [
                        'employee_position_id' => $positionId,
                        'benefit_id' => $benefitIds[$benefitName],
                    ],
                    [
                        'amount' => $amount,
                        'is_active' => true,
                        'users_id' => $userId,
                    ]
                );
            }
        }
    }

    private function seedCuts(int $positionId, array $values, array $cutIds, int $userId)
    {
        foreach ($values as $cutName => $amount) {
            if (isset($cutIds[$cutName])) {
                MasterEmployeePositionSalaryCut::updateOrCreate(
                    [
                        'employee_position_id' => $positionId,
                        'salary_cuts_id' => $cutIds[$cutName],
                    ],
                    [
                        'amount' => $amount,
                        'is_active' => true,
                        'users_id' => $userId,
                    ]
                );
            }
        }
    }
}
