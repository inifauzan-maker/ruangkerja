<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsoAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['sso.enabled' => true, 'sso.server_url' => 'https://sso.example.test', 'sso.client_id' => 'ruangkerja', 'sso.client_secret' => 'test-secret', 'sso.redirect_uri' => 'https://ruangkerja.example.test/sso/callback']);
        Http::preventStrayRequests();
    }

    public function test_login_redirect_uses_state_and_pkce_without_exposing_secret(): void
    {
        $response = $this->get(route('sso.login'))->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('ruangkerja', $query['client_id']);
        $this->assertSame('S256', $query['code_challenge_method']);
        $this->assertSame(session('sso.attempt.state'), $query['state']);
        $this->assertArrayNotHasKey('client_secret', $query);
    }

    public function test_callback_provisions_sso_identity_as_normal_user_with_workspace(): void
    {
        $this->fakeIdentity();
        $this->withSession($this->attempt())->get($this->callbackUrl())->assertRedirect(route('dashboard'));
        $user = User::where('sso_id', '123')->firstOrFail();
        $this->assertSame(User::RoleUser, $user->global_role);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('teams', ['owner_id' => $user->id]);
        $this->assertDatabaseCount('board_lists', 4);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://sso.example.test/oauth/token' && $request['code_verifier'] === 'verifier' && $request['client_secret'] === 'test-secret');
        $this->get($this->callbackUrl())->assertRedirect(route('dashboard'));
    }

    public function test_existing_account_is_linked_without_changing_its_role(): void
    {
        $user = User::factory()->create(['email' => 'person@example.test', 'global_role' => User::RoleSuperAdmin]);
        $this->fakeIdentity();
        $this->withSession($this->attempt())->get($this->callbackUrl())->assertRedirect(route('dashboard'));
        $this->assertSame('123', $user->fresh()->sso_id);
        $this->assertSame(User::RoleSuperAdmin, $user->fresh()->global_role);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_wrong_or_expired_state_is_rejected_without_network_calls(): void
    {
        $this->withSession($this->attempt())->get('/sso/callback?state=wrong&code=code')->assertRedirect(route('login'))->assertSessionHasErrors('sso');
        $this->withSession(['sso.attempt' => ['state' => 'state', 'verifier' => 'verifier', 'started_at' => now()->timestamp - 301]])->get($this->callbackUrl())->assertRedirect(route('login'));
        Http::assertNothingSent();
        $this->assertGuest();
    }

    public function test_failed_code_exchange_does_not_create_or_log_in_user(): void
    {
        Http::fake(['https://sso.example.test/oauth/token' => Http::response(['error' => 'invalid_grant'], 400)]);
        $this->withSession($this->attempt())->get($this->callbackUrl())->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
        Http::assertSentCount(1);
    }

    public function test_mismatched_linked_identity_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'person@example.test']);
        $user->sso_id = 'other-id';
        $user->save();
        $this->fakeIdentity();
        $this->withSession($this->attempt())->get($this->callbackUrl())->assertSessionHasErrors('sso');
        $this->assertGuest();
        $this->assertSame('other-id', $user->fresh()->sso_id);
    }

    public function test_missing_configuration_does_not_redirect_to_sso(): void
    {
        config(['sso.enabled' => false]);
        $this->get(route('sso.login'))->assertRedirect(route('login'))->assertSessionHasErrors('sso');
        Http::assertNothingSent();
    }

    public function test_inactive_account_is_not_linked_or_reactivated(): void
    {
        $user = User::factory()->create(['email' => 'person@example.test', 'is_active' => false]);
        $this->fakeIdentity();
        $this->withSession($this->attempt())->get($this->callbackUrl())->assertSessionHasErrors('sso');
        $this->assertGuest();
        $this->assertFalse($user->fresh()->is_active);
        $this->assertNull($user->fresh()->sso_id);
        Http::assertSentCount(2);
    }

    public function test_sso_mode_hides_and_blocks_public_registration(): void
    {
        $this->get(route('login'))->assertSee('Masuk dengan akun SIVMI')->assertDontSee('Buat ruang kerja');
        $this->get(route('register'))->assertNotFound();
        $this->post(route('register.store'), ['name' => 'Public User', 'email' => 'public@example.test', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertNotFound();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_profile_is_rejected_without_creating_user(): void
    {
        Http::fake([
            'https://sso.example.test/oauth/token' => Http::response(['access_token' => 'test-access-token']),
            'https://sso.example.test/api/sso/user' => Http::response(['id' => '123', 'name' => 'User', 'email' => 'invalid-email']),
        ]);
        $this->withSession($this->attempt())->get($this->callbackUrl())->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        Http::assertSentCount(2);
    }

    public function test_callback_requires_validated_code_and_state(): void
    {
        $this->from(route('login'))->get(route('sso.callback'))->assertRedirect(route('login'))->assertSessionHasErrors(['state', 'code']);
        $this->assertGuest();
        Http::assertNothingSent();
    }

    private function fakeIdentity(): void
    {
        Http::fake([
            'https://sso.example.test/oauth/token' => Http::response(['access_token' => 'test-access-token']),
            'https://sso.example.test/api/sso/user' => Http::response(['id' => '123', 'name' => 'SSO User', 'email' => 'person@example.test', 'global_role' => 'superadmin']),
        ]);
    }

    private function attempt(): array
    {
        return ['sso.attempt' => ['state' => 'state', 'verifier' => 'verifier', 'started_at' => now()->timestamp]];
    }

    private function callbackUrl(): string
    {
        return '/sso/callback?state=state&code=code';
    }
}
