<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_users_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'password',
            'is_active' => false,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ] + $this->botGuardPayload())->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_successful_login_records_last_login_time(): void
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'password' => 'password',
            'is_active' => true,
            'last_login_at' => null,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ] + $this->botGuardPayload())->assertRedirect();

        $this->assertNotNull($user->fresh()->last_login_at);
    }
}
