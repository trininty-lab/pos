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

        $admin = User::updateOrCreate(
            ['email' => 'cybertrinity6@gmail.com'],
            [
                'name' => 'Cyber Trinity',
                'password' => Hash::make('password'),
            ]
        );

        $ownerRole = Role::where('name', 'owner')->first();
        $permissions = Permission::all();

        if ($ownerRole) {
            $admin->syncRoles([$ownerRole->name]);
        }

        $admin->syncPermissions($permissions);

        $cashier = User::updateOrCreate(
            ['email' => 'charitymainda@gmail.com'],
            [
                'name' => 'Charity Mainda',
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
