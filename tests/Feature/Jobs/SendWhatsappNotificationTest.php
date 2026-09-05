<?php

namespace Tests\Feature\Jobs;

use App\Actions\SendWhatsappMessage;
use App\Jobs\SendWhatsappNotification;
use App\Models\User;
use App\Models\WhatsappConnection;
use App\Models\WhatsappNotificationLog;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SendWhatsappNotificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_records_successful_delivery_in_notification_log(): void
    {
        config()->set('services.fonnte.api_key', 'fonnte-global-device-key-123456');
        Http::preventStrayRequests();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true, 'id' => ['80367170']]),
        ]);
        $connection = WhatsappConnection::factory()->for(User::factory())->create();
        $log = WhatsappNotificationLog::factory()->for($connection, 'connection')->create();
        $job = $this->makeJob($connection, $log);

        $job->handle(app(SendWhatsappMessage::class));

        $log->refresh();
        $this->assertSame(WhatsappNotificationLog::StatusSent, $log->status);
        $this->assertSame('80367170', $log->message_id);
        $this->assertNotNull($log->sent_at);
    }

    public function test_records_failed_delivery_without_exposing_credentials(): void
    {
        config()->set('services.fonnte.api_key', 'fonnte-global-secret-key-not-for-logs-123456');
        Http::preventStrayRequests();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => false, 'reason' => 'token invalid']),
        ]);
        $connection = WhatsappConnection::factory()->for(User::factory())->create();
        $log = WhatsappNotificationLog::factory()->for($connection, 'connection')->create();
        $job = $this->makeJob($connection, $log);

        try {
            $job->handle(app(SendWhatsappMessage::class));
            $this->fail('Job seharusnya melempar RuntimeException.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Gagal mengirim WhatsApp: token invalid', $exception->getMessage());
        }

        $log->refresh();
        $this->assertSame(WhatsappNotificationLog::StatusFailed, $log->status);
        $this->assertStringNotContainsString('fonnte-global-secret-key', (string) $log->error_message);
        $this->assertNotNull($log->error_at);
        $this->assertSame(3, $job->tries);
        $this->assertSame([10, 60, 300], $job->backoff);
    }

    private function makeJob(WhatsappConnection $connection, WhatsappNotificationLog $log): SendWhatsappNotification
    {
        return new SendWhatsappNotification(
            $connection->id,
            WhatsappConnection::EventTaskCreated,
            'Tugas baru',
            'Siapkan materi demo',
            'Peluncuran',
            'http://localhost/boards/1',
            $log->id,
        );
    }
}
