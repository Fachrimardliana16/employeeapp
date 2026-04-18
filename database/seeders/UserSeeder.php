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
    public function run(): void
    {
        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Create default Permissions
        $defaultPermissions = [
            'access_admin_panel',
            'access_employee_panel',
            'access_user_panel',
            'view_any_activity_log', // Added specific for activity log
        ];

        // Gather all resources across panels
        $resources = [];
        $panels = ['Admin', 'Employee', 'User'];
        foreach ($panels as $panel) {
            $path = app_path('Filament/' . $panel . '/Resources');
            if (file_exists($path)) {
                $files = glob($path . '/*.php');
                foreach ($files as $file) {
                    $className = basename($file, '.php');
                    if (str_ends_with($className, 'Resource')) {
                        // Convert UserResource to user
                        $slug = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', str_replace('Resource', '', $className)));
                        $resources[] = $slug;
                    }
                }
            }
        }
        $resources = array_unique($resources);

        // Define CRUD actions
        $actions = ['view_any', 'view', 'create', 'update', 'delete', 'restore', 'force_delete'];

        // Create resource specific permissions
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}_{$resource}", 'guard_name' => 'web']);
            }
        }

        // Create default permissions
        foreach ($defaultPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign all permissions to superadmin
        $superAdminRole->syncPermissions(Permission::all());

        // Assign specific permissions to admin (example for Employee resource)
        $adminRole->syncPermissions(
            Permission::where('name', 'access_employee_panel')
                ->orWhere('name', 'like', '%_employee')
                ->get()
        );

        // Assign specific permissions to user (example)
        $userRole->syncPermissions([
            'access_user_panel',
        ]);

        $this->command->info('Roles and permissions initialized successfully!');
    }
}
