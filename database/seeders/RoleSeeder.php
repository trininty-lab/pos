<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // Refactor the RoleSeeder to improve readability and avoid repetitive code
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->normalizeLegacyPermissionRole();

        $this->createRoleWithPermissions('users-access', '%users%');
        $this->createRoleWithPermissions('roles-access', '%roles%');
        $this->createRoleWithPermissions('permissions-access', '%permissions%');
        $this->createRoleWithPermissions('categories-access', '%categories%');
        $this->createRoleWithPermissions('products-access', '%products%');
        $this->createRoleWithPermissions('pricing-rules-access', '%pricing-rules%');
        $this->createRoleWithPermissions('customers-access', '%customers%');
        $this->createRoleWithPermissions('customer-vouchers-access', '%customer-vouchers%');
        $this->createRoleWithPermissions('customer-segments-access', '%customer-segments%');
        $this->createRoleWithPermissions('crm-campaigns-access', '%crm-campaigns%');
        $this->createRoleWithPermissions('crm-reminders-access', '%crm-reminders%');
        $this->createRoleWithPermissions('transactions-access', '%transactions%');
        $this->createRoleWithPermissions('transactions-confirm-payment', 'transactions-confirm-payment');
        $this->createRoleWithPermissions('layaways-access', '%layaways%');
        $this->createRoleWithPermissions('receivables-access', '%receivables%');
        $this->createRoleWithPermissions('payables-access', '%payables%');
        $this->createRoleWithPermissions('suppliers-access', '%suppliers%');
        $this->createRoleWithPermissions('reports-access', '%reports%');
        $this->createRoleWithPermissions('profits-access', '%profits%');
        $this->createRoleWithPermissions('payment-settings-access', '%payment-settings%');
        $this->createRoleWithPermissions('payment-settings-update', 'payment-settings-update');
        $this->createRoleWithPermissions('stock-opnames-access', '%stock-opnames%');
        $this->createRoleWithPermissions('stock-mutations-access', '%stock-mutations%');
        $this->createRoleWithPermissions('sales-returns-access', '%sales-returns%');
        $this->createRoleWithPermissions('cashier-shifts-access', '%cashier-shifts%');
        $this->createRoleWithPermissions('audit-logs-access', '%audit-logs%');
        $this->createRoleWithPermissions('purchase-orders-access', '%purchase-orders%');
        $this->createRoleWithPermissions('goods-receivings-access', '%goods-receivings%');
        $this->createRoleWithPermissions('supplier-returns-access', '%supplier-returns%');
        $this->createRoleWithPermissions('stock-transfers-access', '%stock-transfers%');
        $this->createRoleWithPermissions('products-import', '%products-import%');
        $this->createRoleWithPermissions('products-export', '%products-export%');
        $this->createRoleWithPermissions('customers-import', '%customers-import%');
        $this->createRoleWithPermissions('customers-export', '%customers-export%');
        $this->createRoleWithPermissions('discounts-approve', 'discounts-approve');
        $this->createRoleWithPermissions('price-lists-access', '%price-lists%');
        $this->createRoleWithPermissions('warehouses-access', '%warehouses%');

        $this->createRoleWithPermissions('dine-tables-access', '%dine-tables%');
        $this->createRoleWithPermissions('dine-orders-access', '%dine-orders%');

        $superAdminRole = Role::updateOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'web'],
            ['scope' => 'system'],
        );
        $superAdminRole->syncPermissions(Permission::all());

        // Owner manages one or more businesses, never system administration.
        $ownerRole = Role::updateOrCreate(
            ['name' => 'owner', 'guard_name' => 'web'],
            ['scope' => 'business'],
        );
        $ownerRole->syncPermissions(Permission::all());

        $this->createRoleWithPermissionPatterns('admin', ['%']);
        $this->createRoleWithPermissionPatterns('manager', [
            '%transactions%', '%products%', '%customers%', '%reports%', '%inventory%', '%cashier-shifts%',
        ]);
        $this->createRoleWithPermissionPatterns('accountant', [
            '%transactions%', '%receivables%', '%payables%', '%reports%', '%profits%', '%audit-logs%',
        ]);
        $this->createRoleWithPermissionPatterns('storekeeper', [
            '%products%', '%categories%', '%warehouses%', '%stock%', '%purchase-orders%', '%goods-receivings%', '%supplier-returns%', '%stock-transfers%',
        ]);

        // Create cashier role with basic permissions for public registration
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $cashierPermissions = Permission::whereIn('name', [
            'dashboard-access',
            'transactions-access',
            'cashier-shifts-access',
            'cashier-shifts-open',
            'cashier-shifts-close',
            'customers-access',
            'customers-create',
            'layaways-access',
            'layaways-create',
            'layaways-pay',
            'layaways-release',
            'layaways-cancel',
            'receivables-access',
            'receivables-pay',
            'payables-access',
            'payables-pay',
            'suppliers-access',
        ])->get();
        $cashierRole->syncPermissions($cashierPermissions);

        $this->removeSystemPermissionsFromBusinessRoles();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function normalizeLegacyPermissionRole(): void
    {
        $legacyRole = Role::where('name', 'permission-access')->first();

        if (! $legacyRole) {
            return;
        }

        $finalRole = Role::firstOrCreate([
            'name' => 'permissions-access',
            'guard_name' => $legacyRole->guard_name,
        ]);

        if (DB::getSchemaBuilder()->hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->where('role_id', $legacyRole->id)
                ->update(['role_id' => $finalRole->id]);
        }

        if (DB::getSchemaBuilder()->hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')
                ->where('role_id', $legacyRole->id)
                ->update(['role_id' => $finalRole->id]);
        }

        $legacyRole->delete();
    }

    private function createRoleWithPermissions($roleName, $permissionNamePattern)
    {
        $permissions = Permission::where('name', 'like', $permissionNamePattern)->get();
        $role = Role::updateOrCreate(
            ['name' => $roleName, 'guard_name' => 'web'],
            ['scope' => 'business'],
        );
        $role->syncPermissions($permissions);
    }

    private function createRoleWithPermissionPatterns(string $roleName, array $patterns): void
    {
        $permissions = Permission::query()
            ->where(function ($query) use ($patterns) {
                foreach ($patterns as $pattern) {
                    $query->orWhere('name', 'like', $pattern);
                }
            })
            ->get();

        Role::updateOrCreate(
            ['name' => $roleName, 'guard_name' => 'web'],
            ['scope' => 'business'],
        )->syncPermissions($permissions);
    }

    private function removeSystemPermissionsFromBusinessRoles(): void
    {
        $systemPermissions = Permission::whereIn('name', [
            'permissions-access',
        ])->get();

        Role::where('scope', '!=', 'system')->get()->each(function (Role $role) use ($systemPermissions): void {
            $role->revokePermissionTo($systemPermissions);
        });
    }
}
