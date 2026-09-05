<?php

namespace Tests\Feature\Whatsapp;

use App\Models\User;
use App\Models\WhatsappConnection;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappConnectionManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_from_whatsapp_settings(): void
    {
        $this->get(route('profile.whatsapp.show'))->assertRedirect(route('login'));
    }

    public function test_settings_page_explains_that_api_key_is_managed_globally(): void
    {
        $user = User::factory()->create();
        WhatsappConnection::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('profile.whatsapp.show'));

        $response->assertSee('API key Fonnte dikelola secara global oleh administrator.');
        $response->assertDontSee('name="api_key"', false);
    }

    public function test_user_can_save_recipient_and_notification_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.whatsapp.update'), [
            'recipient_phone' => '+628123456789',
            'is_active' => '1',
            'consent_whatsapp' => '1',
            'notify_task_created' => '1',
            'notify_announcements' => '1',
        ]);

        $response->assertRedirect(route('profile.whatsapp.show'))
            ->assertSessionHas('status', 'Koneksi Fonnte berhasil disimpan.');

        $connection = $user->whatsappConnection()->firstOrFail();
        $this->assertSame('628123456789', $connection->recipient_phone);
        $this->assertSame('fonnte', $connection->phone_number_id);
        $this->assertTrue($connection->is_active);
        $this->assertFalse($connection->notify_task_updated);
    }

    public function test_user_can_disable_their_connection(): void
    {
        $user = User::factory()->create();
        $connection = WhatsappConnection::factory()->for($user)->create();

        $this->actingAs($user)->put(route('profile.whatsapp.update'), [
            'recipient_phone' => '+628111111111',
        ])->assertRedirect(route('profile.whatsapp.show'));

        $connection->refresh();
        $this->assertFalse($connection->is_active);
    }

    public function test_new_connection_rejects_invalid_phone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.whatsapp.update'), [
            'recipient_phone' => '0812',
        ]);

        $response->assertSessionHasErrors(['recipient_phone']);
        $this->assertNull($user->whatsappConnection);
    }

    public function test_user_can_send_a_fonnte_test_message(): void
    {
        config()->set('services.fonnte.api_key', 'fonnte-global-device-key-123456');
        Http::preventStrayRequests();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'id' => ['80367170'],
                'process' => 'pending',
            ]),
        ]);
        $user = User::factory()->create(['name' => 'Dina']);
        $connection = WhatsappConnection::factory()->for($user)->create([
            'recipient_phone' => '628123456789',
        ]);

        $response = $this->actingAs($user)->post(route('profile.whatsapp.test'));

        $response->assertRedirect()->assertSessionHas('status', 'Pesan uji WhatsApp berhasil dikirim melalui Fonnte.');
        $connection->refresh();
        $this->assertNotNull($connection->last_tested_at);
        $this->assertSame('80367170', $connection->last_message_id);
        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request->hasHeader('Authorization', 'fonnte-global-device-key-123456')
                && $request['target'] === '628123456789'
                && str_contains($request['message'], 'Tes koneksi TimManager')
                && $request['countryCode'] === '0';
        });
    }

    public function test_failed_fonnte_test_message_is_reported_without_deleting_connection(): void
    {
        config()->set('services.fonnte.api_key', 'fonnte-global-device-key-123456');
        Http::preventStrayRequests();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'token invalid',
            ]),
        ]);
        $user = User::factory()->create();
        $connection = WhatsappConnection::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('profile.whatsapp.test'));

        $response->assertRedirect()->assertSessionHasErrors('whatsapp');
        $connection->refresh();
        $this->assertNotNull($connection->last_error_at);
        $this->assertSame('Gagal mengirim WhatsApp: token invalid', $connection->last_error_message);
        $this->assertModelExists($connection);
    }

    public function test_test_message_reports_missing_global_api_key_without_sending_request(): void
    {
        config()->set('services.fonnte.api_key');
        Http::preventStrayRequests();
        $user = User::factory()->create();
        $connection = WhatsappConnection::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('profile.whatsapp.test'));

        $response->assertRedirect()->assertSessionHasErrors([
            'whatsapp' => 'API key Fonnte global belum dikonfigurasi oleh administrator.',
        ]);
        $this->assertNotNull($connection->fresh()->last_error_at);
        Http::assertNothingSent();
    }

    public function test_user_can_disconnect_their_whatsapp_api(): void
    {
        $user = User::factory()->create();
        $connection = WhatsappConnection::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('profile.whatsapp.destroy'));

        $response->assertRedirect(route('profile.whatsapp.show'))
            ->assertSessionHas('status', 'Koneksi Fonnte berhasil dihapus.');
        $this->assertModelMissing($connection);
    }

    public function test_user_can_save_due_reminder_and_quiet_hours_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.whatsapp.update'), [
            'recipient_phone' => '+628123456789',
            'notify_due_reminders' => '1',
            'quiet_hours_enabled' => '1',
            'timezone' => 'Asia/Makassar',
            'quiet_hours_start' => '22:30',
            'quiet_hours_end' => '06:15',
        ]);

        $response->assertRedirect(route('profile.whatsapp.show'));
        $this->assertDatabaseHas('whatsapp_connections', [
            'user_id' => $user->id,
            'notify_due_reminders' => true,
            'quiet_hours_enabled' => true,
            'timezone' => 'Asia/Makassar',
            'quiet_hours_start' => '22:30',
            'quiet_hours_end' => '06:15',
        ]);
    }

    public function test_rejects_invalid_quiet_hours_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.whatsapp.update'), [
            'recipient_phone' => '+628123456789',
            'timezone' => 'Mars/Olympus',
            'quiet_hours_start' => '25:00',
            'quiet_hours_end' => 'pagi',
        ]);

        $response->assertSessionHasErrors(['timezone', 'quiet_hours_start', 'quiet_hours_end']);
        $this->assertNull($user->whatsappConnection);
    }
}
