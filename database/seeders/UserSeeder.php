<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $owner = User::updateOrCreate(
            ['email' => 'cybertrinity6@gmail.com'],
            [
                'name' => 'POS Owner',
                'password' => Hash::make('password'),
            ]
        );

        $ownerRole = Role::where('name', 'owner')->first();

        if ($ownerRole) {
            $owner->syncRoles([$ownerRole->name]);
        }

        $owner->syncPermissions([]);

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@pos.test'],
            [
                'name' => 'POS Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        $superAdminRole = Role::where('name', 'super-admin')->first();

        if ($superAdminRole) {
            $superAdmin->syncRoles([$superAdminRole->name]);
        }

        $superAdmin->syncPermissions([]);

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@gmail.com'],
            [
                'name' => 'POS Cashier',
                'password' => Hash::make('Cheri@2026'),
            ]
        );

        $cashierRole = Role::where('name', 'cashier')->first();

        if ($cashierRole) {
            $cashier->syncRoles([$cashierRole->name]);
            $cashier->syncPermissions([]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        $transactionsPermission = Permission::where('name', 'transactions-access')->first();
        $cashier->syncPermissions($transactionsPermission ? [$transactionsPermission] : []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
