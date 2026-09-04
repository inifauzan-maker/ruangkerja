<x-layout title="Pencarian">
    <div class="min-h-dvh bg-[#f4f5f3]">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex min-h-16 max-w-6xl flex-wrap items-center gap-3 px-4 py-3 sm:px-6">
                <a href="{{ route('dashboard') }}" class="grid h-10 w-10 place-items-center rounded-xl bg-[#f2b84b] font-extrabold text-[#153d36]">R</a>
                <form method="GET" action="{{ route('search') }}" class="flex min-w-0 flex-1 gap-2"><input type="search" name="q" value="{{ $term }}" required maxlength="100" autofocus class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-600" placeholder="Cari tugas, proyek, atau file..."><button class="rounded-xl bg-[#153d36] px-4 py-2.5 text-sm font-bold text-white">Cari</button></form>
                <x-current-user-avatar class="h-10 w-10 text-xs" />
            </div>
        </header>
        <main class="mx-auto max-w-6xl space-y-7 px-4 py-7 sm:px-6">
            <div><p class="text-sm font-semibold text-emerald-700">Pencarian global</p><h1 class="mt-1 text-2xl font-extrabold">Hasil untuk "{{ $term }}"</h1><p class="mt-2 text-sm text-slate-400">{{ $boards->count() + $tasks->count() + $attachments->count() }} hasil ditemukan.</p></div>

            <section><h2 class="mb-3 font-extrabold">Proyek <span class="text-sm text-slate-400">{{ $boards->count() }}</span></h2><div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@forelse ($boards as $resultBoard)<a href="{{ route('boards.show', $resultBoard) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300"><p class="text-xs font-bold text-emerald-700">{{ $resultBoard->team->name }}</p><h3 class="mt-1 font-extrabold">{{ $resultBoard->name }}</h3><p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ $resultBoard->description }}</p></a>@empty<p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-400">Tidak ada proyek yang cocok.</p>@endforelse</div></section>

            <section><h2 class="mb-3 font-extrabold">Tugas <span class="text-sm text-slate-400">{{ $tasks->count() }}</span></h2><div class="grid gap-3 sm:grid-cols-2">@forelse ($tasks as $resultTask)<a href="{{ route('boards.tasks.show', [$resultTask->list->board, $resultTask]) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300"><div class="flex items-center gap-2"><span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase">{{ $resultTask->priority }}</span><span class="truncate text-xs text-slate-400">{{ $resultTask->list->board->name }} / {{ $resultTask->list->title }}</span></div><h3 class="mt-3 font-extrabold">{{ $resultTask->title }}</h3><p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ $resultTask->description }}</p></a>@empty<p class="col-span-full rounded-xl bg-white p-5 text-sm text-slate-400">Tidak ada tugas yang cocok.</p>@endforelse</div></section>

            <section><h2 class="mb-3 font-extrabold">File <span class="text-sm text-slate-400">{{ $attachments->count() }}</span></h2><div class="grid gap-2">@forelse ($attachments as $attachment)<a href="{{ route('attachments.preview', $attachment) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-300"><span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50">📎</span><span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold">{{ $attachment->original_name }}</span><span class="text-xs text-slate-400">{{ $attachment->formattedSize() }}</span></span><span class="text-xs font-bold text-emerald-700">Unduh</span></a>@empty<p class="rounded-xl bg-white p-5 text-sm text-slate-400">Tidak ada file yang cocok.</p>@endforelse</div></section>
        </main>
    </div>
</x-layout>
