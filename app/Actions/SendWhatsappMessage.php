<?php

namespace App\Actions;

use App\Models\WhatsappConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SendWhatsappMessage
{
    /**
     * @param  array{event: string, subject: string, project: string, url: string}  $parameters
     */
    public function execute(WhatsappConnection $connection, array $parameters): string
    {
        $apiKey = config('services.fonnte.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('API key Fonnte global belum dikonfigurasi oleh administrator.');
        }

        $message = implode("\n", [
            '*'.$parameters['event'].'*',
            $parameters['subject'],
            'Proyek: '.$parameters['project'],
            'Buka: '.$parameters['url'],
        ]);

        $response = Http::withHeaders(['Authorization' => trim($apiKey)])
            ->acceptJson()
            ->asForm()
            ->connectTimeout(5)
            ->timeout(15)
            ->post((string) config('services.fonnte.endpoint'), [
                'target' => $connection->recipient_phone,
                'message' => Str::limit($message, 60_000, ''),
                'countryCode' => '0',
                'connectOnly' => 'true',
            ]);

        if ($response->failed() || $response->json('status') === false || $response->json('Status') === false) {
            $reason = $response->json('reason') ?? $response->json('detail') ?? 'Penyedia WhatsApp menolak permintaan.';

            throw new RuntimeException('Gagal mengirim WhatsApp: '.Str::limit((string) $reason, 350));
        }

        $messageId = $response->json('id.0') ?? $response->json('id') ?? $response->json('requestid');

        if ((! is_string($messageId) && ! is_int($messageId)) || (string) $messageId === '') {
            throw new RuntimeException('Penyedia WhatsApp tidak mengembalikan ID pesan.');
        }

        return (string) $messageId;
    }
}
