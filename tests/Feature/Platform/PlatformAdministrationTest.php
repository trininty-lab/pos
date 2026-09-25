<?php

namespace Tests\Feature\Platform;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_view_platform_dashboard(): void
    {
        $superAdmin = User::factory()->create();
        $owner = User::factory()->create();
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'owner', 'guard_name' => 'web']);
        $superAdmin->assignRole('super-admin');
        $owner->assignRole('owner');

        $this->actingAs($superAdmin)->get(route('platform.dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('platform.dashboard'))->assertForbidden();
    }

    public function test_super_admin_can_create_and_suspend_business(): void
    {
        $superAdmin = User::factory()->create();
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin)
            ->post(route('platform.businesses.store'), [
                'name' => 'Acme Retail',
                'registration_number' => 'ACME-001',
                'country' => 'KE',
                'currency' => 'KES',
                'status' => 'active',
            ])
            ->assertRedirect();

        $business = Business::where('registration_number', 'ACME-001')->firstOrFail();

        $this->actingAs($superAdmin)
            ->patch(route('platform.businesses.status', $business), ['status' => 'suspended'])
            ->assertRedirect();

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'suspended',
        ]);
    }

    public function test_business_owner_assignment_requires_an_owner_role(): void
    {
        $superAdmin = User::factory()->create();
        $candidate = User::factory()->create();
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'owner', 'guard_name' => 'web']);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin)
            ->post(route('platform.businesses.store'), [
                'name' => 'Invalid Owner Business',
                'country' => 'KE',
                'currency' => 'KES',
                'owner_id' => $candidate->id,
            ])
            ->assertSessionHasErrors('owner_id');
    }

    public function test_business_owner_is_linked_to_their_business_for_user_management(): void
    {
        $superAdmin = User::factory()->create();
        $owner = User::factory()->create();
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'owner', 'guard_name' => 'web']);
        $superAdmin->assignRole('super-admin');
        $owner->assignRole('owner');

        $response = $this->actingAs($superAdmin)
            ->post(route('platform.businesses.store'), [
                'name' => 'Owner Linked Business',
                'country' => 'KE',
                'currency' => 'KES',
                'owner_id' => $owner->id,
                'status' => 'active',
            ]);

        $response->assertRedirect();
        $business = Business::where('name', 'Owner Linked Business')->firstOrFail();

        $this->assertSame($business->id, $owner->fresh()->business_id);
    }
}
