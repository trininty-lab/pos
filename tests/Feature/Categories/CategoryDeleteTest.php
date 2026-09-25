<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_category_cannot_be_deleted_while_products_are_assigned_to_it(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create([
            'name' => 'super-admin',
            'guard_name' => 'web',
            'scope' => 'system',
        ]));

        $category = Category::create([
            'name' => 'Food',
            'description' => 'Food category',
            'image' => 'default.jpg',
        ]);

        Product::create([
            'category_id' => $category->id,
            'title' => 'Rice Bowl',
            'description' => 'A rice bowl',
            'image' => 'default.jpg',
            'barcode' => 'RB-001',
            'buy_price' => 1000,
            'sell_price' => 1500,
            'stock' => 10,
        ]);

        $this->actingAs($user)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', ['category_id' => $category->id]);
    }
}
