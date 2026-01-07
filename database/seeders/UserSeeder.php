<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin role if it doesn't exist
        $superAdminRole = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'web'
        ]);

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@mail.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign superadmin role to admin user
        if (!$adminUser->hasRole('superadmin')) {
            $adminUser->assignRole($superAdminRole);
        }

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@mail.com');
        $this->command->info('Password: password');
        $this->command->info('Role: superadmin');
    }
}
