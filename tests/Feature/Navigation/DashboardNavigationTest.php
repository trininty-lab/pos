<?php

namespace Tests\Feature\Navigation;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every named route exposed by resources/js/Utils/Menu.jsx and the
     * permission required for it to be visible in the sidebar.
     */
    private const MENU_ROUTES = [
        'dashboard' => 'dashboard-access',
        'categories.index' => 'categories-access',
        'products.index' => 'products-access',
        'customers.index' => 'customers-access',
        'suppliers.index' => 'suppliers-access',
        'transactions.index' => 'transactions-access',
        'transactions.history' => 'transactions-access',
        'sales-returns.index' => 'sales-returns-access',
        'receivables.index' => 'receivables-access',
        'aging.index' => 'receivables-access',
        'discount-approvals.pending' => 'discounts-approve',
        'stock-opnames.index' => 'stock-opnames-access',
        'stock-mutations.index' => 'stock-mutations-access',
        'stock-transfers.index' => 'stock-transfers-access',
        'purchase-orders.index' => 'purchase-orders-access',
        'goods-receivings.index' => 'goods-receivings-access',
        'supplier-returns.index' => 'supplier-returns-access',
        'payables.index' => 'payables-access',
        'members.index' => 'customers-access',
        'pricing-rules.index' => 'pricing-rules-access',
        'customer-vouchers.index' => 'customer-vouchers-access',
        'customer-segments.index' => 'customer-segments-access',
        'crm-campaigns.index' => 'crm-campaigns-access',
        'crm-reminders.index' => 'crm-reminders-access',
        'reports.sales.index' => 'reports-access',
        'reports.profits.index' => 'profits-access',
        'reports.insights.index' => 'reports-access',
        'cashier-shifts.index' => 'cashier-shifts-access',
        'audit-logs.index' => 'audit-logs-access',
        'permissions.index' => 'permissions-access',
        'roles.index' => 'roles-access',
        'users.index' => 'users-access',
        'users.create' => 'users-create',
        'settings.payments.edit' => 'payment-settings-access',
        'settings.store' => 'dashboard-access',
        'settings.bank-accounts.index' => 'payment-settings-access',
        'settings.loyalty' => 'dashboard-access',
        'settings.target' => 'dashboard-access',
        'price-lists.index' => 'price-lists-access',
        'settings.warehouses.index' => 'warehouses-access',
        'settings.whatsapp' => 'whatsapp-settings-access',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_every_sidebar_destination_loads_for_a_user_with_its_required_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create([
            'name' => 'super-admin',
            'guard_name' => 'web',
            'scope' => 'system',
        ]));
        $user->givePermissionTo(Permission::all());

        foreach (self::MENU_ROUTES as $routeName => $permission) {
            $request = $this->actingAs($user)
                ->withSession($this->recentlyConfirmedSession());

            $request
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_cashier_destinations_load_and_restricted_destinations_are_denied(): void
    {
        $cashier = User::factory()->create();
        $cashier->givePermissionTo([
            'dashboard-access',
            'transactions-access',
            'cashier-shifts-access',
            'customers-access',
            'receivables-access',
            'payables-access',
            'suppliers-access',
        ]);

        foreach (self::MENU_ROUTES as $routeName => $permission) {
            $response = $this->actingAs($cashier)
                ->withSession($this->recentlyConfirmedSession())
                ->get(route($routeName));

            if ($cashier->can($permission)) {
                $response->assertOk();
            } else {
                $response->assertForbidden();
            }
        }
    }
}
