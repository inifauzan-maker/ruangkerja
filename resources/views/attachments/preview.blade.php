<x-layout :title="$attachment->original_name">
    <div class="min-h-dvh bg-[#f4f5f3]">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex min-h-16 max-w-6xl items-center gap-3 px-4 py-3 sm:px-6">
                <a href="{{ url()->previous() }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500">←</a>
                <div class="min-w-0 flex-1"><p class="truncate text-xs font-bold text-emerald-700">{{ $board->name }}</p><h1 class="truncate font-extrabold">{{ $attachment->original_name }}</h1></div>
                <a href="{{ route('attachments.show', $attachment) }}" class="rounded-xl bg-[#153d36] px-4 py-2.5 text-sm font-bold text-white">Download</a>
            </div>
        </header>

        @if (session('status'))<div data-toast class="fixed right-5 top-20 z-50 rounded-xl bg-[#153d36] px-4 py-3 text-sm font-semibold text-white shadow-xl">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="fixed right-5 top-20 z-50 max-w-sm rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-xl">{{ $errors->first() }}</div>@endif

        <main class="mx-auto grid max-w-6xl gap-5 px-4 py-6 sm:px-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 p-4 text-xs text-slate-500"><span class="rounded-full bg-emerald-50 px-2.5 py-1 font-bold text-emerald-700">v{{ $attachment->version }}</span><span>{{ $attachment->formattedSize() }}</span><span>•</span><span>{{ $attachment->mime_type }}</span><span class="ml-auto rounded-full px-2 py-1 font-bold {{ $attachment->scan_status === 'clean' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $attachment->scan_status === 'clean' ? 'Aman' : ($attachment->scan_status === 'unscanned' ? 'Scanner nonaktif' : 'Scan '.$attachment->scan_status) }}</span></div>
                <div class="grid min-h-[55vh] place-items-center bg-slate-100 p-3 sm:p-5">
                    @if (str_starts_with($attachment->mime_type, 'image/'))
                        <img src="{{ route('attachments.inline', $attachment) }}" alt="{{ $attachment->original_name }}" class="max-h-[75vh] max-w-full rounded-lg object-contain shadow">
                    @elseif ($attachment->mime_type === 'application/pdf')
                        <iframe src="{{ route('attachments.inline', $attachment) }}" title="Preview {{ $attachment->original_name }}" class="h-[70vh] w-full rounded-lg bg-white"></iframe>
                    @elseif ($textPreview !== null)
                        <pre class="max-h-[70vh] w-full overflow-auto whitespace-pre-wrap rounded-xl bg-slate-950 p-5 text-xs leading-6 text-slate-100">{{ $textPreview }}</pre>
                    @else
                        <div class="max-w-md text-center"><span class="text-5xl">📄</span><h2 class="mt-4 text-xl font-extrabold">Preview langsung belum tersedia</h2><p class="mt-2 text-sm leading-6 text-slate-500">Format ini tetap tersimpan aman. Download file untuk membukanya dengan aplikasi yang sesuai.</p></div>
                    @endif
                </div>
            </section>

            <aside class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-extrabold">Upload versi baru</h2><p class="mt-1 text-xs leading-5 text-slate-400">Ekstensi harus sama. Versi lama tetap tersimpan dalam riwayat.</p>
                    <form method="POST" action="{{ route('attachments.versions.store', $attachment) }}" enctype="multipart/form-data" class="mt-4 grid gap-3">@csrf<input type="file" name="attachment" required class="min-w-0 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:font-bold"><button class="rounded-xl bg-[#153d36] px-4 py-2.5 text-sm font-bold text-white">Upload versi</button></form>
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-extrabold">Riwayat versi</h2>
                    <div class="mt-4 grid gap-2">@foreach ($versions as $version)<a href="{{ route('attachments.preview', $version) }}" class="flex items-center gap-3 rounded-xl {{ $version->id === $attachment->id ? 'bg-emerald-50 ring-1 ring-emerald-200' : 'bg-slate-50' }} p-3"><span class="grid h-9 w-9 place-items-center rounded-lg bg-white text-xs font-extrabold text-emerald-700">v{{ $version->version }}</span><span class="min-w-0 flex-1"><span class="block truncate text-xs font-bold">{{ $version->original_name }}</span><span class="text-[10px] text-slate-400">{{ $version->uploader->name }} · {{ $version->created_at->diffForHumans() }}</span></span>@if ($version->is_current)<span class="text-[9px] font-bold uppercase text-emerald-700">Aktif</span>@endif</a>@endforeach</div>
                </section>
                @if ($board->team->canManageProjects(auth()->user()))
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-extrabold">Izin download proyek</h2><form method="POST" action="{{ route('boards.file-settings.update', $board) }}" class="mt-3 grid gap-3">@csrf @method('PATCH')<select name="download_permission" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm"><option value="team" @selected($board->download_permission === 'team')>Semua anggota tim</option><option value="managers" @selected($board->download_permission === 'managers')>Admin dan pengunggah</option><option value="uploader" @selected($board->download_permission === 'uploader')>Owner dan pengunggah</option></select><button class="rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-700">Simpan izin</button></form></section>
                @endif
            </aside>
        </main>
    </div>
</x-layout>
