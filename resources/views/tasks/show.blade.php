<x-layout :title="$task->title">
    @php
        $activityLabels = [
            'created' => 'membuat tugas',
            'updated' => 'memperbarui tugas',
            'assignees_updated' => 'mengubah penanggung jawab',
            'checklist_added' => 'menambahkan checklist',
            'checklist_completed' => 'menyelesaikan checklist',
            'checklist_updated' => 'memperbarui checklist',
            'checklist_removed' => 'menghapus checklist',
            'comment_added' => 'menambahkan komentar',
            'comment_removed' => 'menghapus komentar',
        ];
        $completedChecklist = $task->checklistItems->where('is_completed', true)->count();
        $checklistProgress = $task->checklistItems->isEmpty() ? 0 : (int) round($completedChecklist / $task->checklistItems->count() * 100);
    @endphp

    <div class="min-h-dvh bg-[#f4f5f3]">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6">
                <a href="{{ route('boards.show', $board) }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50" aria-label="Kembali">←</a>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-bold text-emerald-700">{{ $board->team->name }} / {{ $board->name }}</p>
                    <h1 class="truncate font-extrabold">{{ $task->title }}</h1>
                </div>
                <a href="{{ route('search', ['q' => $task->title]) }}" class="hidden rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500 sm:block">Cari terkait</a>
                <x-current-user-avatar class="h-10 w-10 text-xs" />
            </div>
        </header>

        @if (session('status'))
            <div data-toast class="fixed right-5 top-20 z-50 rounded-xl bg-[#153d36] px-4 py-3 text-sm font-semibold text-white shadow-xl">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="fixed right-5 top-20 z-50 max-w-sm rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-xl">{{ $errors->first() }}</div>
        @endif

        <main class="mx-auto grid max-w-7xl gap-5 px-4 py-6 sm:px-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,.7fr)]">
            <div class="min-w-0 space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <form method="POST" action="{{ route('boards.tasks.update', [$board, $task]) }}" class="grid gap-5">
                        @csrf
                        @method('PATCH')
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-700">Detail tugas</p>
                                <p class="mt-2 text-sm text-slate-400">Dibuat oleh {{ $task->creator->name }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">{{ $task->list->title }}</span>
                        </div>
                        <label class="grid gap-2 text-sm font-bold">Judul
                            <input name="title" required maxlength="150" value="{{ old('title', $task->title) }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100">
                        </label>
                        <label class="grid gap-2 text-sm font-bold">Deskripsi
                            <textarea name="description" maxlength="2000" rows="4" class="resize-y rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100">{{ old('description', $task->description) }}</textarea>
                        </label>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <label class="grid gap-2 text-sm font-bold">Status
                                <select name="board_list_id" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">@foreach ($board->lists as $list)<option value="{{ $list->id }}" @selected($list->id === $task->board_list_id)>{{ $list->title }}</option>@endforeach</select>
                            </label>
                            <label class="grid gap-2 text-sm font-bold">Prioritas
                                <select name="priority" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3"><option value="low" @selected($task->priority === 'low')>Rendah</option><option value="medium" @selected($task->priority === 'medium')>Sedang</option><option value="high" @selected($task->priority === 'high')>Tinggi</option></select>
                            </label>
                            <label class="grid gap-2 text-sm font-bold">Tenggat
                                <input type="date" name="due_at" value="{{ $task->due_at?->format('Y-m-d') }}" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            </label>
                        </div>
                        <button class="justify-self-start rounded-xl bg-[#153d36] px-5 py-3 text-sm font-bold text-white hover:bg-[#205148]">Simpan perubahan</button>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div><h2 class="font-extrabold">Checklist</h2><p class="mt-1 text-xs text-slate-400">{{ $completedChecklist }}/{{ $task->checklistItems->count() }} selesai</p></div>
                        <span class="text-sm font-extrabold text-emerald-700">{{ $checklistProgress }}%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-600" style="width: {{ $checklistProgress }}%"></div></div>
                    <div class="mt-5 grid gap-2">
                        @foreach ($task->checklistItems as $item)
                            <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                <form method="POST" action="{{ route('boards.tasks.checklist.update', [$board, $task, $item]) }}">@csrf @method('PATCH')<input type="hidden" name="is_completed" value="{{ $item->is_completed ? 0 : 1 }}"><button class="grid h-6 w-6 place-items-center rounded-md border {{ $item->is_completed ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 bg-white' }}" aria-label="Ubah status checklist">{{ $item->is_completed ? '✓' : '' }}</button></form>
                                <span class="min-w-0 flex-1 text-sm font-semibold {{ $item->is_completed ? 'text-slate-400 line-through' : 'text-slate-700' }}">{{ $item->title }}</span>
                                <form method="POST" action="{{ route('boards.tasks.checklist.destroy', [$board, $task, $item]) }}">@csrf @method('DELETE')<button class="text-sm text-slate-300 hover:text-rose-600" aria-label="Hapus checklist">×</button></form>
                            </div>
                        @endforeach
                    </div>
                    <form method="POST" action="{{ route('boards.tasks.checklist.store', [$board, $task]) }}" class="mt-4 flex gap-2">@csrf<input name="title" required maxlength="255" placeholder="Tambahkan checklist..." class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-600"><button class="rounded-xl bg-emerald-50 px-4 text-sm font-bold text-emerald-700">Tambah</button></form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="font-extrabold">File tugas</h2>
                    <x-attachments :attachments="$task->attachments" />
                    <form method="POST" action="{{ route('boards.tasks.attachments.store', [$board, $task]) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 sm:grid-cols-[1fr_auto]">@csrf<input type="file" name="attachments[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.zip" class="min-w-0 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:font-bold"><button class="rounded-xl bg-[#153d36] px-4 py-2 text-sm font-bold text-white">Upload file</button><p class="text-[11px] text-slate-400 sm:col-span-2">Maksimal 5 file, masing-masing 10 MB.</p></form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="font-extrabold">Komentar</h2>
                    <form method="POST" action="{{ route('boards.tasks.comments.store', [$board, $task]) }}" class="mt-4 grid gap-3">@csrf<textarea name="body" required maxlength="3000" rows="3" placeholder="Tulis komentar..." class="resize-y rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-600"></textarea><details class="rounded-xl border border-slate-200 p-3"><summary class="cursor-pointer text-xs font-bold text-slate-500">Mention anggota</summary><div class="mt-3 flex flex-wrap gap-2">@foreach ($teamMembers as $member)<label class="cursor-pointer rounded-full bg-slate-100 px-3 py-2 text-xs font-semibold"><input type="checkbox" name="mention_ids[]" value="{{ $member->id }}" class="mr-1 accent-emerald-700"> {{ '@'.$member->name }}</label>@endforeach</div></details><button class="justify-self-start rounded-xl bg-[#153d36] px-5 py-2.5 text-sm font-bold text-white">Kirim komentar</button></form>
                    <div class="mt-6 grid gap-3">
                        @forelse ($task->comments as $comment)
                            <article class="rounded-xl bg-slate-50 p-4"><div class="flex items-start gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-[#153d36] text-[10px] font-extrabold text-white">{{ str($comment->user->name)->substr(0, 2)->upper() }}</span><div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><span class="text-sm font-bold">{{ $comment->user->name }}</span><span class="text-[10px] text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>@if ($comment->user_id === auth()->id() || $board->team->canManageProjects(auth()->user()))<form method="POST" action="{{ route('boards.tasks.comments.destroy', [$board, $task, $comment]) }}" class="ml-auto">@csrf @method('DELETE')<button class="text-xs text-slate-300 hover:text-rose-600">Hapus</button></form>@endif</div><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $comment->body }}</p>@if ($comment->mentions->isNotEmpty())<div class="mt-2 flex flex-wrap gap-1">@foreach ($comment->mentions as $mentioned)<span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700">{{ '@'.$mentioned->name }}</span>@endforeach</div>@endif</div></div></article>
                        @empty
                            <p class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-400">Belum ada komentar.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-extrabold">Penanggung jawab</h2>
                    <form method="POST" action="{{ route('boards.tasks.assignees.update', [$board, $task]) }}" class="mt-4 grid gap-2">@csrf @method('PUT')
                        @foreach ($teamMembers as $member)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 text-sm font-semibold"><input type="checkbox" name="assignee_ids[]" value="{{ $member->id }}" @checked($task->assignees->contains('id', $member->id)) class="h-4 w-4 rounded accent-emerald-700"><span class="grid h-8 w-8 place-items-center rounded-full bg-[#153d36] text-[9px] font-extrabold text-white">{{ str($member->name)->substr(0, 2)->upper() }}</span><span class="truncate">{{ $member->name }}</span></label>
                        @endforeach
                        <button class="mt-2 rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-700">Simpan assignee</button>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-extrabold">Riwayat aktivitas</h2>
                    <div class="mt-4 grid gap-4">
                        @forelse ($task->activities as $activity)
                            <div class="relative pl-5 text-sm before:absolute before:left-0 before:top-1.5 before:h-2 before:w-2 before:rounded-full before:bg-emerald-500"><p class="leading-5 text-slate-600"><span class="font-bold text-slate-800">{{ $activity->actor?->name ?? 'Sistem' }}</span> {{ $activityLabels[$activity->type] ?? str($activity->type)->replace('_', ' ') }}@if (filled($activity->metadata['title'] ?? null)) <span class="font-semibold">"{{ $activity->metadata['title'] }}"</span>@endif</p><p class="mt-1 text-[10px] text-slate-400">{{ $activity->created_at->diffForHumans() }}</p></div>
                        @empty
                            <p class="text-sm text-slate-400">Belum ada aktivitas.</p>
                        @endforelse
                    </div>
                </section>

                <form method="POST" action="{{ route('boards.tasks.destroy', [$board, $task]) }}" onsubmit="return confirm('Hapus tugas ini?')">@csrf @method('DELETE')<button class="w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-600">Hapus tugas</button></form>
            </aside>
        </main>
    </div>
</x-layout>
