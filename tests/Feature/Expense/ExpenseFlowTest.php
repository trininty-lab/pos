<?php

namespace Tests\Feature\Expense;

use App\Models\Business;
use App\Models\CashierShift;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ExpenseFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['expenses-view', 'expenses-create', 'expenses-submit', 'expenses-approve', 'expenses-reject', 'expenses-cancel', 'expenses-report'] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    public function test_cashier_can_submit_expense_and_manager_can_approve_it(): void
    {
        $business = Business::create([
            'name' => 'Test Business',
            'country' => 'KE',
            'currency' => 'KES',
            'status' => 'active',
        ]);

        $owner = User::factory()->create(['business_id' => $business->id]);
        $cashier = User::factory()->create(['business_id' => $business->id]);
        $owner->givePermissionTo(['expenses-view', 'expenses-approve', 'expenses-reject', 'expenses-report']);
        $cashier->givePermissionTo(['expenses-create', 'expenses-submit', 'expenses-view']);

        $warehouse = Warehouse::create([
            'code' => 'WH-001',
            'name' => 'Main Store',
            'type' => 'main',
            'address' => 'Jl. Test',
            'phone' => '08123456789',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $category = ExpenseCategory::create([
            'business_id' => $owner->business_id,
            'name' => 'Transport',
            'description' => 'Transport related',
            'is_active' => true,
            'created_by' => $cashier->id,
        ]);

        CashierShift::create([
            'user_id' => $cashier->id,
            'opened_by' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'expected_cash' => 100000,
            'warehouse_id' => $warehouse->id,
            'status' => 'open',
        ]);

        $response = $this
            ->actingAs($cashier)
            ->post(route('expenses.store'), [
                'expense_category_id' => $category->id,
                'warehouse_id' => $warehouse->id,
                'amount' => 35000,
                'expense_date' => '2026-09-26',
                'payment_method' => 'cash',
                'description' => 'Transport to collect packaging materials',
                'reference_number' => 'REF-EXP-1',
            ]);

        $response->assertRedirect();
        $expense = Expense::first();
        $this->assertNotNull($expense);
        $this->assertSame('submitted', $expense->status);
        $this->assertSame(35000, $expense->amount);
        $this->assertSame('EXP-2026-000001', $expense->expense_number);

        $approveResponse = $this
            ->actingAs($owner)
            ->post(route('expenses.approve', $expense), [
                'notes' => 'Approved after review',
            ]);

        $approveResponse->assertRedirect();
        $expense->refresh();
        $this->assertSame('approved', $expense->status);
        $this->assertNotNull($expense->approved_by);
        $this->assertSame($owner->id, $expense->approved_by);
        $this->assertSame(35000, $expense->cash_effect);
    }

    public function test_owner_can_only_see_expenses_for_their_business(): void
    {
        $businessA = Business::create([
            'name' => 'Business A',
            'country' => 'KE',
            'currency' => 'KES',
            'status' => 'active',
        ]);

        $businessB = Business::create([
            'name' => 'Business B',
            'country' => 'KE',
            'currency' => 'KES',
            'status' => 'active',
        ]);

        $ownerA = User::factory()->create(['business_id' => $businessA->id]);
        $cashierA = User::factory()->create(['business_id' => $businessA->id]);
        $ownerB = User::factory()->create(['business_id' => $businessB->id]);

        $ownerA->givePermissionTo(['expenses-view', 'expenses-approve', 'expenses-reject', 'expenses-report']);
        $cashierA->givePermissionTo(['expenses-create', 'expenses-submit', 'expenses-view']);
        $ownerB->givePermissionTo(['expenses-view']);

        $warehouseA = Warehouse::create([
            'code' => 'WH-BIZ-A',
            'name' => 'Business A Warehouse',
            'type' => 'main',
            'address' => 'Jl. A',
            'phone' => '08100000001',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $warehouseB = Warehouse::create([
            'code' => 'WH-BIZ-B',
            'name' => 'Business B Warehouse',
            'type' => 'branch',
            'address' => 'Jl. B',
            'phone' => '08100000002',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $categoryA = ExpenseCategory::create([
            'business_id' => $businessA->id,
            'name' => 'Office Supplies',
            'description' => 'Office cost',
            'is_active' => true,
            'created_by' => $cashierA->id,
        ]);

        $categoryB = ExpenseCategory::create([
            'business_id' => $businessB->id,
            'name' => 'Marketing',
            'description' => 'Marketing cost',
            'is_active' => true,
            'created_by' => $ownerB->id,
        ]);

        Expense::create([
            'business_id' => $businessA->id,
            'warehouse_id' => $warehouseA->id,
            'created_by' => $cashierA->id,
            'expense_category_id' => $categoryA->id,
            'expense_number' => 'EXP-2026-000101',
            'currency' => 'KES',
            'amount' => 25000,
            'payment_method' => 'cash',
            'expense_date' => '2026-09-26',
            'description' => 'A4 paper for store',
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => $cashierA->id,
            'cash_effect' => 25000,
        ]);

        Expense::create([
            'business_id' => $businessB->id,
            'warehouse_id' => $warehouseB->id,
            'created_by' => $ownerB->id,
            'expense_category_id' => $categoryB->id,
            'expense_number' => 'EXP-2026-000102',
            'currency' => 'KES',
            'amount' => 60000,
            'payment_method' => 'bank_transfer',
            'expense_date' => '2026-09-26',
            'description' => 'Ad placement',
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => $ownerB->id,
            'cash_effect' => 0,
        ]);

        $response = $this->actingAs($ownerA)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertSee('EXP-2026-000101');
        $response->assertDontSee('EXP-2026-000102');
    }

    public function test_rejected_expense_keeps_reason_and_does_not_approve_automatically(): void
    {
        $business = Business::create([
            'name' => 'Second Business',
            'country' => 'KE',
            'currency' => 'KES',
            'status' => 'active',
        ]);

        $cashier = User::factory()->create(['business_id' => $business->id]);
        $manager = User::factory()->create(['business_id' => $business->id]);
        $cashier->givePermissionTo(['expenses-create', 'expenses-submit', 'expenses-view']);
        $manager->givePermissionTo(['expenses-view', 'expenses-approve', 'expenses-reject', 'expenses-report']);

        $warehouse = Warehouse::create([
            'code' => 'WH-002',
            'name' => 'Branch Store',
            'type' => 'branch',
            'address' => 'Jl. Branch',
            'phone' => '08111111111',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $category = ExpenseCategory::create([
            'business_id' => $cashier->business_id,
            'name' => 'Utilities',
            'description' => 'Bill utilities',
            'is_active' => true,
            'created_by' => $cashier->id,
        ]);

        $expense = Expense::create([
            'business_id' => $cashier->business_id,
            'warehouse_id' => $warehouse->id,
            'created_by' => $cashier->id,
            'expense_category_id' => $category->id,
            'expense_number' => 'EXP-2026-000002',
            'currency' => 'KES',
            'amount' => 15000,
            'payment_method' => 'cash',
            'expense_date' => '2026-09-26',
            'description' => 'Electricity bill',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this
            ->actingAs($manager)
            ->post(route('expenses.reject', $expense), [
                'reason' => 'Receipt not provided',
            ]);

        $response->assertRedirect();
        $expense->refresh();
        $this->assertSame('rejected', $expense->status);
        $this->assertSame('Receipt not provided', $expense->rejection_reason);
        $this->assertSame($manager->id, $expense->rejected_by);
    }
}
