<?php

namespace Tests\Feature\Layaway;

use App\Models\Cart;
use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Layaway;
use App\Models\LayawayPayment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LayawayFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['transactions-access', 'cashier-shifts-access', 'cashier-shifts-open', 'cashier-shifts-close', 'layaways-access', 'layaways-create', 'layaways-pay', 'layaways-release', 'layaways-cancel'] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    public function test_cashier_can_create_layaway_with_initial_payment_and_reserve_inventory(): void
    {
        $cashier = $this->createCashier();
        $this->openShiftFor($cashier);
        $customer = Customer::create([
            'name' => 'Layaway Customer',
            'no_telp' => '08123456789',
            'address' => 'Jl. Layaway',
        ]);
        $product = $this->createProduct();

        $response = $this
            ->actingAs($cashier)
            ->post(route('layaways.store'), [
                'customer_id' => $customer->id,
                'items' => [[
                    'product_id' => $product->id,
                    'qty' => 1,
                    'unit_price' => $product->sell_price,
                ]],
                'initial_payment' => 20000,
                'payment_method' => 'cash',
                'payment_reference' => 'REF-001',
                'notes' => 'DP awal',
            ]);

        $response->assertRedirect();
        $layaway = Layaway::query()->first();
        $this->assertNotNull($layaway);
        $this->assertSame('partial', $layaway->status);
        $this->assertSame($product->sell_price, $layaway->total_amount);
        $this->assertSame(20000, $layaway->paid_amount);
        $this->assertSame($product->sell_price - 20000, $layaway->balance_amount);
        $this->assertDatabaseHas('layaway_payments', ['layaway_id' => $layaway->id, 'amount' => 20000]);
        $this->assertSame(1, $layaway->items()->count());
        $this->assertSame($product->stock - 1, $product->fresh()->stock);
    }

    public function test_cashier_can_add_payment_until_layaway_reaches_zero_balance(): void
    {
        $cashier = $this->createCashier();
        $this->openShiftFor($cashier);
        $customer = Customer::create([
            'name' => 'Layaway Payment Customer',
            'no_telp' => '08199999999',
            'address' => 'Jl. Payment',
        ]);
        $product = $this->createProduct();

        $layaway = Layaway::create([
            'reference' => 'LAY-000001',
            'customer_id' => $customer->id,
            'user_id' => $cashier->id,
            'status' => 'partial',
            'total_amount' => $product->sell_price,
            'paid_amount' => 20000,
            'balance_amount' => $product->sell_price - 20000,
            'notes' => 'test',
        ]);

        $layaway->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => $product->sell_price,
            'subtotal' => $product->sell_price,
        ]);

        $response = $this
            ->actingAs($cashier)
            ->post(route('layaways.payments.store', $layaway), [
                'amount' => $product->sell_price - 20000,
                'payment_method' => 'cash',
                'payment_reference' => 'REF-002',
            ]);

        $response->assertRedirect();
        $layaway->refresh();
        $this->assertSame('paid', $layaway->status);
        $this->assertSame(0, $layaway->balance_amount);
        $this->assertSame($product->sell_price, $layaway->paid_amount);
        $this->assertDatabaseHas('layaway_payments', ['layaway_id' => $layaway->id, 'amount' => $product->sell_price - 20000]);
    }

    protected function createCashier(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo([
            'transactions-access',
            'cashier-shifts-access',
            'cashier-shifts-open',
            'cashier-shifts-close',
            'layaways-access',
            'layaways-create',
            'layaways-pay',
            'layaways-release',
            'layaways-cancel',
        ]);

        return $user;
    }

    protected function openShiftFor(User $cashier)
    {
        return CashierShift::create([
            'user_id' => $cashier->id,
            'opened_by' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'expected_cash' => 100000,
            'status' => 'open',
        ]);
    }

    protected function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Layaway Category',
            'description' => 'Test category',
            'image' => 'category.png',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'image' => 'product.png',
            'barcode' => 'LAY-'.Str::upper(Str::random(10)),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'title' => 'Produk Cicilan',
            'description' => 'Deskripsi produk cicilan',
            'buy_price' => 40000,
            'sell_price' => 60000,
            'stock' => 10,
            'tax_rate' => 0,
        ]);
    }
}
