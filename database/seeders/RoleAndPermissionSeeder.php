<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'view reports',
            'view sales',
            'create sales',
            'cancel sales',
            'view products',
            'create products',
            'update products',
            'delete products',
            'manage inventory',
            'view inventory movements',
            'manage categories',
            'view customers',
            'create customers',
            'update customers',
            'delete customers',
            'manage shifts',
            'manage users',
            'manage roles',
            'manage settings',
            'view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'admin' => $permissions,
            'manager' => [
                'view dashboard', 'view reports', 'view sales', 'cancel sales',
                'view products', 'create products', 'update products', 'delete products',
                'manage inventory', 'view inventory movements', 'manage categories',
                'view customers', 'create customers', 'update customers', 'delete customers',
                'manage shifts', 'view audit logs',
            ],
            'cashier' => [
                'view dashboard', 'view sales', 'create sales', 'cancel sales',
                'view products', 'view customers', 'create customers', 'update customers',
                'manage shifts',
            ],
            'inventory_clerk' => [
                'view products', 'create products', 'update products',
                'manage inventory', 'view inventory movements', 'manage categories',
                'view customers',
            ],
        ];

        foreach ($roles as $role => $perms) {
            $roleModel = Role::firstOrCreate(['name' => $role]);
            $roleModel->syncPermissions($perms);
        }
    }
}
