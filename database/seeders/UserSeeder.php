<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'System Administrator',
                'phone' => '+255700000001',
                'password' => Hash::make('password'),
                'role_name' => 'admin',
                'status' => true,
            ]
        );
        $admin->syncRoles(['admin']);

        $manager = User::firstOrCreate(
            ['email' => 'manager@pos.com'],
            [
                'name' => 'Store Manager',
                'phone' => '+255700000002',
                'password' => Hash::make('password'),
                'role_name' => 'manager',
                'status' => true,
            ]
        );
        $manager->syncRoles(['manager']);

        $clerk = User::firstOrCreate(
            ['email' => 'clerk@pos.com'],
            [
                'name' => 'Inventory Clerk',
                'phone' => '+255700000003',
                'password' => Hash::make('password'),
                'role_name' => 'inventory_clerk',
                'status' => true,
            ]
        );
        $clerk->syncRoles(['inventory_clerk']);

        for ($i = 1; $i <= 10; $i++) {
            $cashier = User::firstOrCreate(
                ['email' => "cashier{$i}@pos.com"],
                [
                    'name' => "Cashier {$i}",
                    'phone' => '+2557000000'.str_pad((string) (10 + $i), 2, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'role_name' => 'cashier',
                    'status' => true,
                ]
            );
            $cashier->syncRoles(['cashier']);
        }
    }
}
