<?php

namespace Tests\Feature\Authorization;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_owner_can_create_a_complete_user_account_for_their_business(): void
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

        $createdUser = User::where('email', 'business-cashier@example.com')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'email' => 'business-cashier@example.com',
            'business_id' => $business->id,
        ]);
        $this->assertTrue(Hash::check('password', $createdUser->password));
        $this->assertTrue($createdUser->hasRole('cashier'));
    }

    public function test_owner_confirms_password_before_opening_the_new_user_form(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::findByName('owner'));
        $business = Business::create(['name' => 'Owner Business', 'status' => 'active', 'owner_id' => $owner->id]);
        $owner->update(['business_id' => $business->id]);

        $this->actingAs($owner)
            ->from(route('users.index'))
            ->get(route('users.create'))
            ->assertRedirect(route('password.confirm'));

        $this->actingAs($owner)
            ->post(route('password.confirm'), ['password' => 'password'])
            ->assertRedirect(route('users.create'));

        $this->actingAs($owner)
            ->post(route('users.store'), [
                'name' => 'Confirmed Cashier',
                'email' => 'confirmed-cashier@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'selectedRoles' => ['cashier'],
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'confirmed-cashier@example.com',
            'business_id' => $business->id,
        ]);
    }

    public function test_owner_can_create_a_user_when_their_business_link_is_missing(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::findByName('owner'));
        $business = Business::create(['name' => 'Repair Business Link', 'status' => 'active', 'owner_id' => $owner->id]);
        $owner->forceFill(['business_id' => null])->saveQuietly();

        $this->actingAs($owner)
            ->withSession($this->recentlyConfirmedSession())
            ->post(route('users.store'), [
                'name' => 'Linked Business Cashier',
                'email' => 'missing-business-link@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'selectedRoles' => ['cashier'],
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'missing-business-link@example.com',
            'business_id' => $business->id,
        ]);
        $this->assertSame($business->id, $owner->fresh()->business_id);
    }

    public function test_business_owner_can_open_user_creation_without_direct_user_permission_assignments(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::findByName('owner'));
        $owner->revokePermissionTo(['users-access', 'users-create']);
        $business = Business::create(['name' => 'Owner Business', 'status' => 'active', 'owner_id' => $owner->id]);
        $owner->update(['business_id' => $business->id]);

        $this->actingAs($owner)
            ->withSession($this->recentlyConfirmedSession())
            ->get(route('users.create'))
            ->assertOk();

        $this->actingAs($owner)
            ->withSession($this->recentlyConfirmedSession())
            ->post(route('users.store'), [
                'name' => 'Role Authorized Cashier',
                'email' => 'role-authorized@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'selectedRoles' => ['cashier'],
            ])
            ->assertRedirect(route('users.index'));
    }

    public function test_user_creation_rejects_unknown_roles_without_creating_an_account(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::findByName('owner'));
        $business = Business::create(['name' => 'Owner Business', 'status' => 'active', 'owner_id' => $owner->id]);
        $owner->update(['business_id' => $business->id]);

        $this->actingAs($owner)
            ->withSession($this->recentlyConfirmedSession())
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Invalid Role User',
                'email' => 'invalid-role@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'selectedRoles' => ['does-not-exist'],
            ])
            ->assertSessionHasErrors('selectedRoles.0');

        $this->assertDatabaseMissing('users', [
            'email' => 'invalid-role@example.com',
        ]);
    }
}