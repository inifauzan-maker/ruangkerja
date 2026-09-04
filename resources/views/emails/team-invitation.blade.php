<x-mail::message>
# Undangan ke {{ $invitation->team->name }}

{{ $invitation->inviter->name }} mengundang Anda sebagai **{{ $invitation->role === 'admin' ? 'Admin' : 'Member' }}** di RuangKerja.

<x-mail::button :url="route('team-invitations.show', [$invitation, $token])">
Lihat undangan
</x-mail::button>

Undangan berlaku sampai {{ $invitation->expires_at->translatedFormat('d F Y, H:i') }}.

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
