<x-layout :title="$board->name">
    @php
        $listStyles = [
            'slate' => ['dot' => 'bg-slate-400', 'soft' => 'bg-slate-100 text-slate-600'],
            'amber' => ['dot' => 'bg-amber-400', 'soft' => 'bg-amber-100 text-amber-700'],
            'emerald' => ['dot' => 'bg-emerald-500', 'soft' => 'bg-emerald-100 text-emerald-700'],
            'rose' => ['dot' => 'bg-rose-500', 'soft' => 'bg-rose-100 text-rose-700'],
            'violet' => ['dot' => 'bg-violet-500', 'soft' => 'bg-violet-100 text-violet-700'],
        ];
    @endphp

    <div class="flex h-dvh overflow-hidden bg-[#f4f5f3]" data-board>
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-[#123A70] text-white transition-transform duration-300 lg:static lg:w-56 lg:translate-x-0 xl:w-60">
            <a href="{{ route('dashboard') }}" class="flex h-20 items-center gap-3 border-b border-white/10 px-6 hover:bg-white/5">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#F5C542] text-xs font-extrabold text-[#123A70]">VM</span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-extrabold tracking-tight">Ruang Kerja _ Villa Merah</p>
                    <p class="text-xs text-blue-100/60">Workspace tim</p>
                </div>
            </a>

            <nav class="flex-1 space-y-1 px-3 py-6 text-sm font-semibold">
                <a href="{{ route('boards.show', $board) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 {{ ($activeSection ?? 'kanban') === 'kanban' ? 'bg-[#C62828] text-white' : 'text-blue-50/70 hover:bg-white/5 hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                    Tugas
                </a>
                <a href="{{ route('boards.chat', $board) }}" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 {{ ($activeSection ?? 'kanban') === 'chat' ? 'bg-[#C62828] text-white' : 'text-blue-50/70 hover:bg-white/5 hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4-4 7-9 7a10 10 0 01-4-.8L3 20l1.4-3.6A6.4 6.4 0 013 12c0-4 4-7 9-7s9 3 9 7z"/></svg>
                    Chat grup
                </a>
                <a href="{{ route('boards.announcements', $board) }}" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 {{ ($activeSection ?? 'kanban') === 'announcements' ? 'bg-[#C62828] text-white' : 'text-blue-50/70 hover:bg-white/5 hover:text-white' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5l7 4v6l-7 4V5zM5 9v6m0-3H3"/></svg>
                    Pengumuman
                </a>

                <p class="px-3 pb-2 pt-8 text-[11px] font-bold uppercase tracking-[.18em] text-blue-100/35">Papan saya</p>
                @foreach ($boards as $availableBoard)
                    <a href="{{ route('boards.show', $availableBoard) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ $availableBoard->is($board) ? 'bg-[#F5C542] text-[#123A70]' : 'text-blue-50/65 hover:bg-white/5 hover:text-white' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $availableBoard->is($board) ? 'bg-[#123A70]' : 'bg-emerald-300/40' }}"></span>
                        <span class="truncate">{{ $availableBoard->name }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="flex items-center gap-3 rounded-xl bg-white/5 p-3">
                    <a href="{{ route('profile.show') }}" title="Profil saya"><x-current-user-avatar class="h-9 w-9 border-2 border-[#F5C542] text-xs" /></a>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-blue-50/45">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button title="Keluar" class="rounded-lg p-2 text-blue-50/50 hover:bg-white/10 hover:text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>

        <main class="flex h-dvh min-w-0 flex-1 flex-col overflow-hidden">
            <header class="shrink-0 border-b border-slate-200 bg-white">
                <div class="flex h-20 items-center gap-4 px-4 sm:px-6 lg:px-8">
                    <button id="sidebar-toggle" class="rounded-xl border border-slate-200 p-2.5 text-slate-600 lg:hidden" aria-label="Buka menu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                            <span>{{ $board->team->name }}</span><span>/</span><span>Papan</span>
                        </div>
                        <h1 class="mt-1 truncate text-xl font-extrabold tracking-tight sm:text-2xl">{{ $board->name }}</h1>
                    </div>
                    <form method="GET" action="{{ route('search') }}" class="relative hidden w-64 md:block">
                        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke-width="2"/></svg>
                        <input name="q" type="search" required maxlength="100" placeholder="Cari semua..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </form>
                    <button class="relative grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50" aria-label="Notifikasi">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m3 0v2"/></svg>
                        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    </button>
                    <a href="{{ route('profile.show') }}" title="Profil saya"><x-current-user-avatar class="h-10 w-10 border-2 border-[#F5C542] text-xs" /></a>
                </div>

                <div class="flex items-center gap-1 overflow-x-auto px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('boards.show', $board) }}" class="whitespace-nowrap border-b-2 px-3 py-3 text-sm {{ ($activeSection ?? 'kanban') === 'kanban' ? 'border-emerald-700 font-bold text-emerald-800' : 'border-transparent font-semibold text-slate-400 hover:text-slate-700' }}">Papan Kanban</a>
                    <a href="{{ route('boards.summary', $board) }}" class="whitespace-nowrap border-b-2 px-3 py-3 text-sm {{ ($activeSection ?? 'kanban') === 'summary' ? 'border-emerald-700 font-bold text-emerald-800' : 'border-transparent font-semibold text-slate-400 hover:text-slate-700' }}">Ringkasan</a>
                    <a href="{{ route('boards.chat', $board) }}" class="whitespace-nowrap border-b-2 px-3 py-3 text-sm {{ ($activeSection ?? 'kanban') === 'chat' ? 'border-emerald-700 font-bold text-emerald-800' : 'border-transparent font-semibold text-slate-400 hover:text-slate-700' }}">Chat</a>
                    <a href="{{ route('boards.announcements', $board) }}" class="whitespace-nowrap border-b-2 px-3 py-3 text-sm {{ ($activeSection ?? 'kanban') === 'announcements' ? 'border-emerald-700 font-bold text-emerald-800' : 'border-transparent font-semibold text-slate-400 hover:text-slate-700' }}">Pengumuman</a>
                    <div class="ml-auto hidden items-center gap-2 pb-2 sm:flex">
                        <span class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">{{ $board->lists->sum(fn ($list) => $list->tasks->count()) }} tugas</span>
                        <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500">{{ $board->lists->count() }} list</span>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div data-toast class="fixed right-5 top-5 z-[70] flex items-center gap-3 rounded-xl bg-[#123A70] px-4 py-3 text-sm font-semibold text-white shadow-xl">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-400 text-[#123A70]">✓</span>{{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="fixed right-5 top-5 z-[80] max-w-sm rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-xl">{{ $errors->first() }}</div>
            @endif

            @if (($activeSection ?? 'kanban') === 'kanban')
                <section class="flex min-h-0 flex-1 flex-col px-3 py-4 sm:px-4 lg:px-5 xl:px-6">
                <div class="mb-4 flex shrink-0 flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Kelola pekerjaan tim</p>
                        <p class="mt-1 text-xs text-slate-400">Seret kartu untuk memindahkan status tugas.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('boards.lists.store', $board) }}" class="flex gap-2">
                            @csrf
                            <input name="title" required maxlength="80" placeholder="List baru..." aria-label="Nama list baru" class="w-32 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:w-44 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 sm:w-40">
                            <button class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-600 hover:border-emerald-700 hover:text-emerald-800">+ List</button>
                        </form>
                        <button data-open-task-modal data-list-id="{{ $board->lists->first()?->id }}" class="inline-flex items-center gap-2 rounded-xl bg-[#123A70] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#1D4E89]">
                            <span class="text-lg leading-none">+</span> Buat tugas
                        </button>
                    </div>
                </div>

                <form method="GET" action="{{ route('boards.show', $board) }}" class="mb-3 grid shrink-0 gap-2 rounded-2xl border border-slate-200 bg-white p-3 sm:grid-cols-2 lg:grid-cols-[minmax(180px,1fr)_repeat(3,minmax(130px,auto))_auto_auto]">
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Cari judul/deskripsi..." class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-600">
                    <select name="assignee" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm"><option value="">Semua anggota</option>@foreach ($teamMembers as $member)<option value="{{ $member->id }}" @selected((string) ($filters['assignee'] ?? '') === (string) $member->id)>{{ $member->name }}</option>@endforeach</select>
                    <select name="priority" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm"><option value="">Semua prioritas</option><option value="high" @selected(($filters['priority'] ?? '') === 'high')>Tinggi</option><option value="medium" @selected(($filters['priority'] ?? '') === 'medium')>Sedang</option><option value="low" @selected(($filters['priority'] ?? '') === 'low')>Rendah</option></select>
                    <select name="due" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm"><option value="">Semua tenggat</option><option value="overdue" @selected(($filters['due'] ?? '') === 'overdue')>Terlambat</option><option value="today" @selected(($filters['due'] ?? '') === 'today')>Hari ini</option><option value="week" @selected(($filters['due'] ?? '') === 'week')>7 hari</option><option value="none" @selected(($filters['due'] ?? '') === 'none')>Tanpa tenggat</option></select>
                    <button class="rounded-xl bg-[#123A70] px-4 py-2 text-sm font-bold text-white">Filter</button>
                    <a href="{{ route('boards.show', $board) }}" class="rounded-xl border border-slate-200 px-4 py-2 text-center text-sm font-bold text-slate-500">Reset</a>
                </form>

                <div class="kanban-scroll grid min-h-0 flex-1 grid-flow-col auto-cols-[minmax(270px,85vw)] items-stretch gap-3 overflow-x-auto pb-2 xl:grid-flow-row xl:grid-cols-4 xl:overflow-x-hidden">
                    @foreach ($board->lists as $list)
                        @php $style = $listStyles[$list->color] ?? $listStyles['slate']; @endphp
                        <article class="kanban-column flex min-h-0 flex-col rounded-2xl border border-slate-200/80 bg-[#e9ebe8] p-2.5" data-list-id="{{ $list->id }}">
                            <header class="flex shrink-0 items-center gap-2 px-1 pb-2.5">
                                <span class="h-2.5 w-2.5 rounded-full {{ $style['dot'] }}"></span>
                                <h2 class="font-extrabold">{{ $list->title }}</h2>
                                <span data-task-count class="rounded-full px-2 py-0.5 text-xs font-bold {{ $style['soft'] }}">{{ $list->tasks->count() }}</span>
                                <button class="ml-auto rounded-lg p-1 text-slate-400 hover:bg-white hover:text-slate-700" aria-label="Menu list">•••</button>
                            </header>

                            <div class="task-dropzone min-h-16 flex-1 space-y-2 overflow-y-auto px-0.5 py-0.5 pr-1.5" data-list-id="{{ $list->id }}">
                                @foreach ($list->tasks as $task)
                                    <div draggable="true" data-task-card data-task-id="{{ $task->id }}" data-update-url="{{ route('boards.tasks.update', [$board, $task]) }}" class="group cursor-grab rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md active:cursor-grabbing">
                                        <div class="flex items-start gap-3">
                                            <div class="min-w-0 flex-1">
                                                <div class="mb-2 flex items-center gap-2">
                                                    <span class="rounded-md px-2 py-1 text-[10px] font-extrabold uppercase tracking-wide {{ $task->priority === 'high' ? 'bg-rose-50 text-rose-600' : ($task->priority === 'low' ? 'bg-sky-50 text-sky-600' : 'bg-amber-50 text-amber-700') }}">{{ $task->priority === 'high' ? 'Prioritas tinggi' : ($task->priority === 'low' ? 'Prioritas rendah' : 'Prioritas sedang') }}</span>
                                                </div>
                                                <h3 class="task-title text-sm font-extrabold leading-5 text-slate-800"><a href="{{ route('boards.tasks.show', [$board, $task]) }}" class="hover:text-blue-700">{{ $task->title }}</a></h3>
                                                <div class="mt-2 flex items-center justify-between gap-2">
                                                    <div class="flex -space-x-1.5">@forelse ($task->assignees->take(4) as $assignee)<span title="{{ $assignee->name }}" class="grid h-6 w-6 place-items-center rounded-full border-2 border-white bg-[#123A70] text-[8px] font-extrabold text-white">{{ str($assignee->name)->substr(0, 2)->upper() }}</span>@empty<span class="text-[10px] font-semibold text-slate-400">Belum ada assignee</span>@endforelse</div>
                                                    <a href="{{ route('boards.tasks.show', [$board, $task]) }}" class="text-[10px] font-bold text-emerald-700">{{ $task->completed_checklist_items_count }}/{{ $task->checklist_items_count }} ✓ · {{ $task->comments_count }} komentar</a>
                                                </div>
                                                @if ($task->description)
                                                    <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-500 xl:line-clamp-1 2xl:line-clamp-2">{{ $task->description }}</p>
                                                @endif
                                                <x-attachments :attachments="$task->attachments" />
                                                <details class="mt-2"><summary class="cursor-pointer list-none text-[10px] font-bold text-emerald-700 hover:text-blue-900">+ Tambah lampiran</summary><form method="POST" action="{{ route('boards.tasks.attachments.store', [$board, $task]) }}" enctype="multipart/form-data" class="mt-2 grid gap-2 rounded-lg bg-slate-50 p-2">@csrf<input type="file" name="attachments[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.zip" class="min-w-0 text-[10px] file:mr-2 file:rounded file:border-0 file:bg-white file:px-2 file:py-1 file:font-bold"><button class="rounded-md bg-[#123A70] px-2 py-1.5 text-[10px] font-bold text-white">Upload</button></form></details>
                                            </div>
                                            <form method="POST" action="{{ route('boards.tasks.destroy', [$board, $task]) }}" onsubmit="return confirm('Hapus tugas ini?')" class="opacity-0 transition group-hover:opacity-100">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-lg p-1.5 text-slate-300 hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus tugas">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0V5h8v2m-9 0 1 12h8l1-12M10 10v6m4-6v6"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                        <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-2.5">
                                            <div class="grid h-7 w-7 place-items-center rounded-full bg-[#123A70] text-[10px] font-extrabold text-white">{{ str($task->creator->name)->substr(0, 2)->upper() }}</div>
                                            <span class="truncate text-xs font-semibold text-slate-500">{{ $task->creator->name }}</span>
                                            @if ($task->due_at)
                                                <span class="ml-auto inline-flex items-center gap-1 text-[11px] font-semibold {{ $task->due_at->isPast() ? 'text-rose-600' : 'text-slate-400' }}">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14H4V6a1 1 0 011-1z"/></svg>
                                                    {{ $task->due_at->format('d M') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button data-open-task-modal data-list-id="{{ $list->id }}" class="mt-2 flex w-full shrink-0 items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold text-slate-500 hover:bg-white hover:text-emerald-800">
                                <span class="text-lg leading-none">+</span> Buat tugas
                            </button>
                        </article>
                    @endforeach

                </div>
                </section>
            @elseif ($activeSection === 'summary')
                @php
                    $allTasks = $board->lists->flatMap->tasks;
                    $completedTasks = $board->lists->firstWhere('title', 'Selesai')?->tasks ?? collect();
                    $overdueTasks = $allTasks->filter(fn ($task) => $task->due_at?->isPast() && ! $completedTasks->contains($task));
                    $progress = $allTasks->isEmpty() ? 0 : (int) round(($completedTasks->count() / $allTasks->count()) * 100);
                @endphp
                <section class="min-h-0 flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
                    <div class="mb-6">
                        <p class="text-sm font-semibold text-emerald-700">Ringkasan proyek</p>
                        <h2 class="mt-1 text-2xl font-extrabold">Satu pandangan untuk seluruh progres</h2>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total tugas</p><p class="mt-3 text-3xl font-extrabold">{{ $allTasks->count() }}</p><p class="mt-2 text-xs text-slate-400">Di {{ $board->lists->count() }} list aktif</p></div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Selesai</p><p class="mt-3 text-3xl font-extrabold text-emerald-700">{{ $completedTasks->count() }}</p><p class="mt-2 text-xs text-slate-400">{{ $progress }}% dari seluruh tugas</p></div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Terlambat</p><p class="mt-3 text-3xl font-extrabold {{ $overdueTasks->isEmpty() ? 'text-slate-900' : 'text-rose-600' }}">{{ $overdueTasks->count() }}</p><p class="mt-2 text-xs text-slate-400">Perlu perhatian tim</p></div>
                        <div class="rounded-2xl bg-[#123A70] p-5 text-white shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-blue-100/50">Progress</p><p class="mt-3 text-3xl font-extrabold">{{ $progress }}%</p><div class="mt-4 h-2 rounded-full bg-white/10"><div class="h-2 rounded-full bg-[#F5C542]" style="width: {{ $progress }}%"></div></div></div>
                    </div>
                    <div class="mt-6 grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="font-extrabold">Distribusi pekerjaan</h3>
                            <div class="mt-5 grid gap-4">
                                @foreach ($board->lists as $list)
                                    @php $percentage = $allTasks->isEmpty() ? 0 : (int) round(($list->tasks->count() / $allTasks->count()) * 100); @endphp
                                    <div><div class="mb-2 flex justify-between text-sm"><span class="font-bold">{{ $list->title }}</span><span class="text-slate-400">{{ $list->tasks->count() }} tugas</span></div><div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-emerald-600" style="width: {{ $percentage }}%"></div></div></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h3 class="font-extrabold">Tenggat terdekat</h3>
                            <div class="mt-4 grid gap-3">
                                @forelse ($allTasks->whereNotNull('due_at')->sortBy('due_at')->take(5) as $task)
                                    <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3"><span class="grid h-9 w-9 place-items-center rounded-lg bg-white text-xs font-extrabold text-emerald-700 shadow-sm">{{ $task->due_at->format('d') }}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-bold">{{ $task->title }}</p><p class="text-xs text-slate-400">{{ $task->due_at->translatedFormat('M Y') }}</p></div></div>
                                @empty
                                    <p class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-400">Belum ada tugas dengan tenggat.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>
            @elseif ($activeSection === 'chat')
                <section class="mx-auto flex min-h-0 w-full max-w-5xl flex-1 flex-col px-4 py-6 sm:px-6 lg:px-8">
                    <div class="mb-5"><p class="text-sm font-semibold text-emerald-700">Chat grup</p><h2 class="mt-1 text-2xl font-extrabold">Percakapan {{ $board->name }}</h2></div>
                    <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div id="chat-messages" class="flex-1 space-y-5 overflow-y-auto p-5 sm:p-7">
                            @forelse ($board->messages->reverse() as $message)
                                <div class="flex gap-3 {{ $message->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $message->user_id === auth()->id() ? 'bg-[#F5C542] text-[#123A70]' : 'bg-[#123A70] text-white' }} text-xs font-extrabold">{{ str($message->user->name)->substr(0, 2)->upper() }}</div>
                                    <div class="max-w-[78%] {{ $message->user_id === auth()->id() ? 'text-right' : '' }}"><div class="mb-1 flex items-center gap-2 {{ $message->user_id === auth()->id() ? 'justify-end' : '' }}"><span class="text-xs font-bold">{{ $message->user->name }}</span><span class="text-[10px] text-slate-400">{{ $message->created_at->format('H:i') }}</span></div>@if ($message->body !== '')<p class="whitespace-pre-line rounded-2xl px-4 py-3 text-left text-sm leading-6 {{ $message->user_id === auth()->id() ? 'rounded-tr-sm bg-[#123A70] text-white' : 'rounded-tl-sm bg-slate-100 text-slate-700' }}">{{ $message->body }}</p>@endif<x-attachments :attachments="$message->attachments" class="{{ $message->user_id === auth()->id() ? 'justify-end' : '' }}" /></div>
                                </div>
                            @empty
                                <div class="grid h-full place-items-center text-center"><div><span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-emerald-50 text-2xl">💬</span><p class="mt-4 font-extrabold">Mulai percakapan tim</p><p class="mt-1 text-sm text-slate-400">Pesan pertama akan tampil di sini.</p></div></div>
                            @endforelse
                        </div>
                        <form method="POST" action="{{ route('boards.messages.store', $board) }}" enctype="multipart/form-data" class="flex gap-2 border-t border-slate-100 p-4 sm:gap-3">
                            @csrf
                            <label class="grid h-11 w-11 shrink-0 cursor-pointer place-items-center rounded-xl border border-slate-200 bg-slate-50 text-lg hover:bg-blue-50" title="Pilih lampiran">📎<input type="file" name="attachments[]" multiple class="sr-only" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.zip"></label>
                            <textarea name="body" maxlength="2000" rows="1" placeholder="Tulis pesan atau kirim file..." class="min-h-11 flex-1 resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100"></textarea>
                            <button class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-[#123A70] text-white hover:bg-[#1D4E89]" aria-label="Kirim pesan"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12 3 4l18 8-18 8 2-8Zm0 0h9"/></svg></button>
                        </form>
                    </div>
                </section>
            @elseif ($activeSection === 'announcements')
                <section class="mx-auto min-h-0 w-full max-w-6xl flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
                    <div class="grid gap-6 lg:grid-cols-[.7fr_1.3fr]">
                        <form method="POST" action="{{ route('boards.announcements.store', $board) }}" enctype="multipart/form-data" class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            @csrf
                            <p class="text-sm font-semibold text-emerald-700">Pengumuman baru</p><h2 class="mt-1 text-xl font-extrabold">Bagikan kabar penting</h2>
                            <div class="mt-5 grid gap-4"><label class="grid gap-2 text-sm font-bold">Judul<input name="title" required maxlength="150" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="Contoh: Jadwal rilis berubah"></label><label class="grid gap-2 text-sm font-bold">Isi<textarea name="body" required maxlength="5000" rows="5" class="resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="Tulis detail pengumuman..."></textarea></label><label class="grid gap-2 text-sm font-bold">Lampiran <span class="font-normal text-slate-400">maks. 5 file, masing-masing 10 MB</span><input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.zip" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:font-bold file:text-blue-700"></label><label class="flex items-center gap-3 text-sm text-slate-600"><input type="checkbox" name="is_pinned" value="1" class="h-4 w-4 rounded accent-blue-700"> Sematkan di bagian atas</label><button class="rounded-xl bg-[#123A70] px-5 py-3 text-sm font-bold text-white hover:bg-[#1D4E89]">Terbitkan pengumuman</button></div>
                        </form>
                        <div><div class="mb-4"><p class="text-sm font-semibold text-emerald-700">Kabar tim</p><h2 class="mt-1 text-2xl font-extrabold">Pengumuman terbaru</h2></div><div class="grid gap-4">
                            @forelse ($board->announcements as $announcement)
                                <article class="rounded-2xl border {{ $announcement->is_pinned ? 'border-amber-300 bg-amber-50/50' : 'border-slate-200 bg-white' }} p-5 shadow-sm"><div class="flex items-start gap-3"><div class="min-w-0 flex-1">@if ($announcement->is_pinned)<span class="mb-3 inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Disematkan</span>@endif<h3 class="text-lg font-extrabold">{{ $announcement->title }}</h3><p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $announcement->body }}</p><x-attachments :attachments="$announcement->attachments" /><div class="mt-4 flex items-center gap-2 border-t border-slate-200/60 pt-3 text-xs text-slate-400"><span class="font-bold text-slate-600">{{ $announcement->author->name }}</span><span>•</span><span>{{ $announcement->created_at->diffForHumans() }}</span></div></div></div></article>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/50 p-12 text-center"><span class="text-3xl">📣</span><p class="mt-3 font-extrabold">Belum ada pengumuman</p><p class="mt-1 text-sm text-slate-400">Kabar penting tim akan terkumpul di sini.</p></div>
                            @endforelse
                        </div></div>
                    </div>
                </section>
            @endif
        </main>
    </div>

    <dialog id="task-modal" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-3xl bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm">
        <form method="POST" action="{{ route('boards.tasks.store', $board) }}" enctype="multipart/form-data" class="p-6 sm:p-7">
            @csrf
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-emerald-700">Tugas baru</p>
                    <h2 class="mt-2 text-2xl font-extrabold">Apa yang perlu dikerjakan?</h2>
                </div>
                <button type="button" data-close-task-modal class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-xl text-slate-500 hover:bg-slate-200">×</button>
            </div>
            <div class="mt-7 grid gap-5">
                <input id="task-list-id" type="hidden" name="board_list_id" value="{{ $board->lists->first()?->id }}">
                <label class="grid gap-2 text-sm font-bold">Judul tugas
                    <input id="task-title" name="title" value="{{ old('title') }}" required maxlength="150" placeholder="Contoh: Buat konsep kampanye" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </label>
                <label class="grid gap-2 text-sm font-bold">Deskripsi <span class="font-normal text-slate-400">(opsional)</span>
                    <textarea name="description" rows="3" maxlength="2000" placeholder="Tambahkan konteks atau detail..." class="resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">{{ old('description') }}</textarea>
                </label>
                <fieldset class="grid gap-2"><legend class="text-sm font-bold">Penanggung jawab <span class="font-normal text-slate-400">(bisa lebih dari satu)</span></legend><div class="grid max-h-32 gap-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2">@foreach ($teamMembers as $member)<label class="flex items-center gap-2 text-xs font-semibold"><input type="checkbox" name="assignee_ids[]" value="{{ $member->id }}" class="rounded accent-blue-700">{{ $member->name }}</label>@endforeach</div></fieldset>
                <label class="grid gap-2 text-sm font-bold">Lampiran <span class="font-normal text-slate-400">maks. 5 file, masing-masing 10 MB</span>
                    <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.webp,.zip" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:font-bold file:text-blue-700">
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-2 text-sm font-bold">Prioritas
                        <select name="priority" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                            <option value="low">Rendah</option><option value="medium" selected>Sedang</option><option value="high">Tinggi</option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm font-bold">Tenggat
                        <input name="due_at" type="date" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                    </label>
                </div>
            </div>
            <div class="mt-7 flex justify-end gap-3">
                <button type="button" data-close-task-modal class="rounded-xl px-4 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-100">Batal</button>
                <button class="rounded-xl bg-[#123A70] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#1D4E89]">Simpan tugas</button>
            </div>
        </form>
    </dialog>
</x-layout>
