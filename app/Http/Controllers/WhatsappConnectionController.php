<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateWhatsappConnectionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappConnectionController extends Controller
{
    public function show(Request $request): View
    {
        $connection = $request->user()->whatsappConnection;

        return view('profile.whatsapp', compact('connection'));
    }

    public function update(UpdateWhatsappConnectionRequest $request): RedirectResponse
    {
        $connection = $request->user()->whatsappConnection;
        $isActive = $request->boolean('is_active');
        $hasFreshConsent = $request->boolean('consent_whatsapp');
        $attributes = [
            'phone_number_id' => 'fonnte',
            'recipient_phone' => preg_replace('/\D/', '', $request->string('recipient_phone')->toString()),
            'template_name' => 'plain_text',
            'template_language' => 'id',
            'is_active' => $isActive,
            'notify_task_created' => $request->boolean('notify_task_created'),
            'notify_task_updated' => $request->boolean('notify_task_updated'),
            'notify_chat_messages' => $request->boolean('notify_chat_messages'),
            'notify_announcements' => $request->boolean('notify_announcements'),
            'notify_due_reminders' => $request->boolean('notify_due_reminders'),
            'quiet_hours_enabled' => $request->boolean('quiet_hours_enabled'),
            'timezone' => $request->input('timezone', $connection?->timezone ?? 'Asia/Jakarta'),
            'quiet_hours_start' => $request->input('quiet_hours_start', $connection?->quiet_hours_start ?? '21:00'),
            'quiet_hours_end' => $request->input('quiet_hours_end', $connection?->quiet_hours_end ?? '07:00'),
            'last_error_at' => null,
            'last_error_message' => null,
        ];

        if ($request->filled('api_key')) {
            $attributes['access_token'] = $request->string('api_key')->trim()->toString();
        }

        if ($isActive && $hasFreshConsent) {
            $attributes['consented_at'] = now();
            $attributes['opted_out_at'] = null;
        } elseif (! $isActive && $connection?->consented_at !== null) {
            $attributes['opted_out_at'] = now();
        }

        if ($connection) {
            $connection->update($attributes);
        } else {
            $request->user()->whatsappConnection()->create($attributes);
        }

        $status = $isActive
            ? 'Koneksi Fonnte berhasil disimpan.'
            : 'Notifikasi WhatsApp dinonaktifkan tanpa menghapus API key.';

        return redirect()->route('profile.whatsapp.show')->with('status', $status);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->whatsappConnection?->delete();

        return redirect()->route('profile.whatsapp.show')->with('status', 'Koneksi Fonnte berhasil dihapus.');
    }
}
