<?php

namespace App\Http\Controllers;

use App\Actions\SendWhatsappMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class WhatsappConnectionTestController extends Controller
{
    public function __invoke(Request $request, SendWhatsappMessage $sendWhatsappMessage): RedirectResponse
    {
        $connection = $request->user()->whatsappConnection;

        if (! $connection || ! $connection->is_active || ! $connection->hasNotificationConsent()) {
            return back()->withErrors(['whatsapp' => 'Aktifkan koneksi dan berikan persetujuan WhatsApp terlebih dahulu.']);
        }

        try {
            $messageId = $sendWhatsappMessage->execute($connection, [
                'event' => 'Tes koneksi TimManager',
                'subject' => 'Koneksi WhatsApp untuk '.$request->user()->name.' berhasil.',
                'project' => 'Akun TimManager',
                'url' => route('dashboard'),
            ]);

            $connection->update([
                'last_tested_at' => now(),
                'last_sent_at' => now(),
                'last_message_id' => $messageId,
                'last_error_at' => null,
                'last_error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $connection->update([
                'last_error_at' => now(),
                'last_error_message' => $exception->getMessage(),
            ]);

            return back()->withErrors(['whatsapp' => $exception->getMessage()]);
        }

        return back()->with('status', 'Pesan uji WhatsApp berhasil dikirim melalui Fonnte.');
    }
}
