<x-layout title="Riwayat WhatsApp">
    <div class="min-h-dvh bg-[#f1f2f0]">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex min-h-16 max-w-7xl items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('profile.whatsapp.show') }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50" aria-label="Kembali ke pengaturan WhatsApp">&larr;</a>
                <div class="min-w-0">
                    <h1 class="truncate font-extrabold">Riwayat WhatsApp</h1>
                    <p class="text-xs text-slate-400">Audit pengiriman notifikasi Anda</p>
                </div>
                <a href="{{ route('dashboard') }}" class="ml-auto rounded-xl bg-[#123A70] px-4 py-2.5 text-sm font-bold text-white">Beranda</a>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            @php
                $cards = [
                    ['label' => 'Terkirim', 'status' => 'sent', 'class' => 'bg-emerald-50 text-emerald-700'],
                    ['label' => 'Menunggu', 'status' => 'pending', 'class' => 'bg-amber-50 text-amber-700'],
                    ['label' => 'Gagal', 'status' => 'failed', 'class' => 'bg-rose-50 text-rose-700'],
                    ['label' => 'Dilewati', 'status' => 'skipped', 'class' => 'bg-slate-100 text-slate-600'],
                ];
            @endphp

            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($cards as $card)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <span class="rounded-full px-2.5 py-1 text-xs font-extrabold {{ $card['class'] }}">{{ $card['label'] }}</span>
                        <p class="mt-4 text-3xl font-black text-slate-900">{{ $statusCounts[$card['status']] ?? 0 }}</p>
                    </article>
                @endforeach
            </section>

            <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
                    <h2 class="text-xl font-extrabold">Aktivitas pengiriman</h2>
                    <p class="mt-1 text-sm text-slate-500">Token dan kredensial tidak pernah dicatat pada riwayat ini.</p>
                </div>

                @forelse ($logs as $log)
                    @php
                        $statusStyle = match ($log->status) {
                            'sent' => ['Terkirim', 'bg-emerald-50 text-emerald-700'],
                            'failed' => ['Gagal', 'bg-rose-50 text-rose-700'],
                            'skipped' => ['Dilewati', 'bg-slate-100 text-slate-600'],
                            default => ['Menunggu', 'bg-amber-50 text-amber-700'],
                        };
                    @endphp
                    <article class="grid gap-4 border-b border-slate-100 px-5 py-5 last:border-b-0 sm:px-7 lg:grid-cols-[minmax(0,1fr)_12rem_9rem] lg:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-extrabold {{ $statusStyle[1] }}">{{ $statusStyle[0] }}</span>
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $log->event_label }}</span>
                            </div>
                            <p class="mt-2 truncate font-extrabold text-slate-900">{{ $log->subject }}</p>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $log->project_name }}</p>
                            @if ($log->error_message)
                                <p class="mt-2 break-words text-xs leading-5 text-rose-600">{{ $log->error_message }}</p>
                            @endif
                        </div>
                        <div class="text-sm text-slate-500">
                            <p class="font-bold text-slate-700">Dijadwalkan</p>
                            <p class="mt-1">{{ $log->scheduled_for->timezone(auth()->user()->whatsappConnection?->timezone ?? config('app.timezone'))->format('d M Y, H:i') }}</p>
                        </div>
                        <a href="{{ $log->url }}" class="inline-flex w-fit items-center rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-emerald-700 hover:bg-blue-50">Buka tugas</a>
                    </article>
                @empty
                    <div class="px-6 py-16 text-center">
                        <p class="text-lg font-extrabold text-slate-700">Belum ada riwayat</p>
                        <p class="mt-2 text-sm text-slate-400">Riwayat akan muncul saat notifikasi berikutnya dijadwalkan.</p>
                    </div>
                @endforelse
            </section>

            @if ($logs->hasPages())
                <div class="mt-6">{{ $logs->links() }}</div>
            @endif
        </main>
    </div>
</x-layout>
