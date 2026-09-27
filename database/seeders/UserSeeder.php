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

        $ownerEmail = env('POS_OWNER_EMAIL', 'fayfaithbitokeld@gmail.com');
        $owner = User::firstWhere('email', $ownerEmail)
            ?? User::firstWhere('email', 'cybertrinity6@gmail.com')
            ?? new User;
        $owner->fill([
            'email' => $ownerEmail,
            'name' => env('POS_OWNER_NAME', 'Faith Bitok'),
            'password' => Hash::make(env('POS_OWNER_PASSWORD') ?: 'password'),
        ]);
        $owner->save();

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
            ['email' => 'charity@fay.com'],
            [
                'name' => 'POS Cashier',
                'password' => Hash::make('faycollection'),
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
