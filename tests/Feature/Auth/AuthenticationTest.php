<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('boards.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_return_localized_error(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'salah-password',
        ]);

        $response->assertSessionHasErrors(['email' => 'Email atau kata sandi tidak sesuai.']);
        $this->assertGuest();
    }

    public function test_registration_creates_default_workspace_and_logs_user_in(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Siti Rahma',
            'email' => 'siti@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('teams', ['name' => 'Tim Siti Rahma']);
        $this->assertDatabaseHas('boards', ['name' => 'Proyek Pertama']);
        $this->assertDatabaseCount('board_lists', 4);
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
