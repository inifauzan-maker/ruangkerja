<x-layout title="Undangan Tim">
    <div class="grid min-h-dvh place-items-center bg-[#f1f2f0] px-4 py-10">
        <main class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-xl sm:p-9">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-[#f2b84b] text-xl font-extrabold text-[#153d36]">{{ str($teamInvitation->team->name)->substr(0, 2)->upper() }}</span>
            <p class="mt-6 text-xs font-extrabold uppercase tracking-[.16em] text-emerald-700">Undangan tim</p>
            <h1 class="mt-2 text-3xl font-extrabold">Gabung ke {{ $teamInvitation->team->name }}?</h1>
            <p class="mt-4 text-sm leading-7 text-slate-500">{{ $teamInvitation->inviter->name }} mengundang Anda sebagai <strong>{{ ucfirst($teamInvitation->role) }}</strong>. Undangan berlaku sampai {{ $teamInvitation->expires_at->translatedFormat('d F Y, H:i') }}.</p>
            <form method="POST" action="{{ route('team-invitations.accept', [$teamInvitation, $token]) }}" class="mt-7">@csrf<button class="w-full rounded-xl bg-[#153d36] px-5 py-3.5 font-bold text-white">Terima dan bergabung</button></form>
            <a href="{{ route('dashboard') }}" class="mt-4 inline-block text-sm font-bold text-slate-400 hover:text-slate-700">Kembali ke beranda</a>
        </main>
    </div>
</x-layout>
