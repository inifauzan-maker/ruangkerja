<?php

namespace Tests\Feature\Whatsapp;

use App\Actions\SendWhatsappMessage;
use App\Jobs\SendWhatsappNotification;
use App\Models\User;
use App\Models\WhatsappConnection;
use App\Models\WhatsappNotificationLog;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappConsentTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_new_active_connection_requires_consent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('profile.whatsapp.update'), [
            'api_key' => 'fonnte-valid-device-key-123456789',
            'recipient_phone' => '+628123456789',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors([
            'consent_whatsapp' => 'Persetujuan WhatsApp wajib diberikan untuk mengaktifkan notifikasi.',
        ]);
        $this->assertNull($user->whatsappConnection);
    }

    public function test_user_can_opt_out_without_deleting_encrypted_api_key(): void
    {
        $user = User::factory()->create();
        $connection = WhatsappConnection::factory()->for($user)->create([
            'access_token' => 'fonnte-existing-device-key-12345',
        ]);

        $response = $this->actingAs($user)->put(route('profile.whatsapp.update'), [
            'api_key' => '',
            'recipient_phone' => '+628111111111',
        ]);

        $response->assertRedirect(route('profile.whatsapp.show'))
            ->assertSessionHas('status', 'Notifikasi WhatsApp dinonaktifkan tanpa menghapus API key.');
        $connection->refresh();
        $this->assertFalse($connection->is_active);
        $this->assertNotNull($connection->opted_out_at);
        $this->assertSame('fonnte-existing-device-key-12345', $connection->access_token);
        $this->assertFalse($connection->hasNotificationConsent());
    }

    public function test_user_must_consent_again_when_reactivating_notifications(): void
    {
        $user = User::factory()->create();
        $connection = WhatsappConnection::factory()->for($user)->create([
            'is_active' => false,
            'consented_at' => now()->subDay(),
            'opted_out_at' => now(),
        ]);

        $this->actingAs($user)->put(route('profile.whatsapp.update'), [
            'api_key' => '',
            'recipient_phone' => '+628123456789',
            'is_active' => '1',
        ])->assertSessionHasErrors('consent_whatsapp');

        $response = $this->actingAs($user)->put(route('profile.whatsapp.update'), [
            'api_key' => '',
            'recipient_phone' => '+628123456789',
            'is_active' => '1',
            'consent_whatsapp' => '1',
        ]);

        $response->assertRedirect(route('profile.whatsapp.show'))
            ->assertSessionHas('status', 'Koneksi Fonnte berhasil disimpan.');
        $connection->refresh();
        $this->assertTrue($connection->is_active);
        $this->assertNull($connection->opted_out_at);
        $this->assertTrue($connection->hasNotificationConsent());
    }

    public function test_queued_job_is_skipped_after_user_opts_out(): void
    {
        Http::preventStrayRequests();
        $connection = WhatsappConnection::factory()->for(User::factory())->create([
            'is_active' => false,
            'opted_out_at' => now(),
        ]);
        $log = WhatsappNotificationLog::factory()->for($connection, 'connection')->create();
        $job = new SendWhatsappNotification(
            $connection->id,
            WhatsappConnection::EventTaskCreated,
            'Tugas baru',
            'Siapkan materi demo',
            'Peluncuran',
            'http://localhost/boards/1',
            $log->id,
        );

        $job->handle(app(SendWhatsappMessage::class));

        $this->assertSame(WhatsappNotificationLog::StatusSkipped, $log->fresh()->status);
        Http::assertNothingSent();
    }
}
