<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterEmployeeFamily;
use App\Models\User;

class MasterEmployeeFamilySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@employapp.com')->first() ?? User::first();

        $relations = [
            ['name' => 'Suami', 'desc' => 'Hubungan sebagai suami'],
            ['name' => 'Istri', 'desc' => 'Hubungan sebagai istri'],
            ['name' => 'Orang Tua', 'desc' => 'Hubungan sebagai orang tua'],
            ['name' => 'Anak', 'desc' => 'Hubungan sebagai anak'],
        ];

        foreach ($relations as $relation) {
            MasterEmployeeFamily::updateOrCreate(
                ['name' => $relation['name']],
                [
                    'desc' => $relation['desc'],
                    'is_active' => true,
                    'users_id' => $user->id,
                ]
            );
        }
    }
}
