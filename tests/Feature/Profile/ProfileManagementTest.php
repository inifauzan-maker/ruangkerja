<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_from_profile_page(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Dina Pratama',
            'job_title' => 'Product Designer',
            'bio' => 'Menyusun pengalaman produk.',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertSeeInOrder(['Profil Saya', 'Dina Pratama', 'Product Designer', 'Menyusun pengalaman produk.']);
    }

    public function test_user_can_update_profile_without_mass_assigning_password(): void
    {
        $user = User::factory()->create([
            'email' => 'lama@example.com',
            'password' => 'password-lama',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'job_title' => 'Project Lead',
            'phone' => '+62 812-3456-7890',
            'bio' => 'Memimpin pekerjaan lintas tim.',
            'password' => 'password-disusupi',
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Profil berhasil diperbarui.');
        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@example.com', $user->email);
        $this->assertSame('Project Lead', $user->job_title);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('password-lama', $user->password));
        $this->assertFalse(Hash::check('password-disusupi', $user->password));
    }

    public function test_profile_rejects_email_used_by_another_user(): void
    {
        User::factory()->create(['email' => 'sudah@example.com']);
        $user = User::factory()->create(['email' => 'sendiri@example.com']);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'sudah@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('sendiri@example.com', $user->fresh()->email);
    }

    public function test_profile_escapes_user_provided_bio(): void
    {
        $user = User::factory()->create(['bio' => '<script>alert("profil")</script>']);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertSee('&lt;script&gt;', false);
        $response->assertDontSee('<script>alert("profil")</script>', false);
    }

    public function test_user_must_provide_current_password_to_change_password(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $response = $this->actingAs($user)->patch(route('profile.password.update'), [
            'current_password' => 'password-salah',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
    }

    public function test_user_can_change_password_with_valid_current_password(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $response = $this->actingAs($user)->patch(route('profile.password.update'), [
            'current_password' => 'password-lama',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ]);

        $response->assertRedirect()->assertSessionHas('status', 'Password berhasil diperbarui.');
        $this->assertTrue(Hash::check('password-baru-123', $user->fresh()->password));
    }

    public function test_user_can_upload_replace_view_and_delete_private_avatar(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('avatar-lama.jpg', 300, 300),
        ])->assertRedirect()->assertSessionHas('status', 'Foto profil berhasil diperbarui.');
        $oldPath = $user->fresh()->avatar_path;
        Storage::disk('local')->assertExists($oldPath);

        $this->actingAs($user)->get(route('profile.avatar.show'))->assertOk();
        $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('avatar-baru.png', 400, 400),
        ])->assertRedirect();

        $user->refresh();
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($user->avatar_path);
        $newPath = $user->avatar_path;

        $this->actingAs($user)->delete(route('profile.avatar.destroy'))
            ->assertRedirect()->assertSessionHas('status', 'Foto profil dihapus.');
        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('local')->assertMissing($newPath);
    }

    public function test_non_image_avatar_is_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->create('avatar.svg', 10, 'image/svg+xml'),
        ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('local')->assertDirectoryEmpty('/');
    }
}
