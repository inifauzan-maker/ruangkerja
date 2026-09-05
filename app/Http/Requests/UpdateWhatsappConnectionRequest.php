<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWhatsappConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $connection = $this->user()?->whatsappConnection;
        $requiresConsent = $this->boolean('is_active')
            && (! $connection?->hasNotificationConsent());

        return [
            'recipient_phone' => ['required', 'string', 'regex:/^\+?[1-9][0-9]{7,14}$/'],
            'is_active' => ['sometimes', 'boolean'],
            'consent_whatsapp' => [Rule::when($requiresConsent, ['required', 'accepted'], ['nullable'])],
            'notify_task_created' => ['sometimes', 'boolean'],
            'notify_task_updated' => ['sometimes', 'boolean'],
            'notify_chat_messages' => ['sometimes', 'boolean'],
            'notify_announcements' => ['sometimes', 'boolean'],
            'notify_due_reminders' => ['sometimes', 'boolean'],
            'quiet_hours_enabled' => ['sometimes', 'boolean'],
            'timezone' => ['sometimes', 'timezone:all'],
            'quiet_hours_start' => ['sometimes', 'date_format:H:i'],
            'quiet_hours_end' => ['sometimes', 'date_format:H:i'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'consent_whatsapp.required' => 'Persetujuan WhatsApp wajib diberikan untuk mengaktifkan notifikasi.',
            'consent_whatsapp.accepted' => 'Persetujuan WhatsApp wajib diberikan untuk mengaktifkan notifikasi.',
            'recipient_phone.regex' => 'Nomor penerima harus memakai format internasional, contoh +628123456789.',
            'timezone.timezone' => 'Zona waktu yang dipilih tidak valid.',
            'quiet_hours_start.date_format' => 'Jam mulai harus memakai format 24 jam.',
            'quiet_hours_end.date_format' => 'Jam selesai harus memakai format 24 jam.',
        ];
    }
}
