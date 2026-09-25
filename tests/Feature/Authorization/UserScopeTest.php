<?php

namespace Tests\Feature\Authorization;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['users-access', 'users-create', 'users-update', 'users-delete'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->seed(RoleSeeder::class);
    }

    public function test_owner_cannot_manage_a_user_from_another_business(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::findByName('owner'));
        $business = Business::create(['name' => 'Owner Business', 'status' => 'active', 'owner_id' => $owner->id]);
        $owner->update(['business_id' => $business->id]);

        $otherBusiness = Business::create(['name' => 'Other Business', 'status' => 'active']);
        $otherUser = User::factory()->create(['business_id' => $otherBusiness->id]);
        $otherUser->assignRole(Role::findByName('cashier'));

        $this->actingAs($owner)
            ->get(route('users.edit', $otherUser))
            ->assertForbidden();
    }

    public function test_owner_created_users_are_assigned_to_the_owners_business(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::findByName('owner'));
        $business = Business::create(['name' => 'Owner Business', 'status' => 'active', 'owner_id' => $owner->id]);
        $owner->update(['business_id' => $business->id]);

        $this->actingAs($owner)
            ->withSession($this->recentlyConfirmedSession())
            ->post(route('users.store'), [
                'name' => 'Business Cashier',
                'email' => 'business-cashier@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'selectedRoles' => ['cashier'],
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'business-cashier@example.com',
            'business_id' => $business->id,
        ]);
    }
}