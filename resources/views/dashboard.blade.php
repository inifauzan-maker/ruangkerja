<x-layout title="Beranda">
    @php
        $ownedTeams = $teams->where('owner_id', auth()->id());
        $managedTeams = $teams->filter(fn ($team) => $team->canManageProjects(auth()->user()));
        $projects = $teams->flatMap->boards;
        $avatarColors = ['bg-emerald-700', 'bg-amber-500', 'bg-violet-600', 'bg-sky-600', 'bg-rose-600'];
    @endphp

    <div class="flex h-dvh flex-col overflow-hidden bg-[#f1f2f0]">
        <header class="shrink-0 border-b border-slate-200 bg-white">
            <div class="flex h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#f2b84b] font-extrabold text-[#153d36]">R</span>
                    <div class="hidden sm:block"><p class="font-extrabold tracking-tight">RuangKerja</p><p class="text-[10px] font-bold uppercase tracking-[.16em] text-slate-400">Beranda</p></div>
                </a>
                <form method="GET" action="{{ route('search') }}" class="mx-auto hidden w-full max-w-md md:block">
                    <label class="relative block"><svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke-width="2"/></svg><input id="dashboard-search" name="q" type="search" required maxlength="100" placeholder="Cari tim, proyek, tugas, file..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100"></label>
                </form>
                <div class="ml-auto flex items-center gap-3">
                    <span class="hidden rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 sm:inline">{{ $teams->count() }} tim · {{ $projects->count() }} proyek</span>
                    <a href="{{ route('reports.index') }}" class="rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-bold text-slate-600 hover:bg-emerald-50 hover:text-emerald-700">Laporan</a>
                    <a href="{{ route('profile.show') }}" title="Profil saya"><x-current-user-avatar class="h-10 w-10 text-xs" /></a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button title="Keluar" class="rounded-xl border border-slate-200 p-2.5 text-slate-400 hover:bg-slate-50 hover:text-rose-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg></button></form>
                </div>
            </div>

            <div class="flex min-h-14 items-center gap-3 overflow-x-auto border-t border-slate-100 px-4 py-2 sm:px-6 lg:px-8">
                <div class="flex -space-x-2">
                    @foreach ($teams->flatMap(fn ($team) => collect([$team->owner])->merge($team->members))->unique('id')->take(5) as $member)
                        <span title="{{ $member->name }}" class="grid h-9 w-9 place-items-center rounded-full border-2 border-white {{ $avatarColors[$loop->index % count($avatarColors)] }} text-[10px] font-extrabold text-white">{{ str($member->name)->substr(0, 2)->upper() }}</span>
                    @endforeach
                </div>
                @if ($managedTeams->isNotEmpty())
                    <a href="{{ route('teams.show', $managedTeams->first()) }}" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100"><span class="text-xl">+</span> Kelola anggota</a>
                @endif
                <div class="ml-auto hidden items-center gap-4 text-xs font-bold text-slate-400 lg:flex"><span>Akses aman</span><span>•</span><span>Workspace tim</span></div>
            </div>
        </header>

        @if (session('status'))
            <div data-toast class="fixed right-5 top-5 z-[70] flex items-center gap-3 rounded-xl bg-[#153d36] px-4 py-3 text-sm font-semibold text-white shadow-xl"><span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-400 text-[#153d36]">✓</span>{{ session('status') }}</div>
        @endif

        <main class="min-h-0 flex-1 overflow-y-auto">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <section>
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-300 pb-4">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-white text-emerald-700 shadow-sm"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm13 10v-2a4 4 0 00-3-3.9m-2-12a4 4 0 010 7.8"/></svg></span>
                        <div><h1 class="text-xl font-extrabold">Tim</h1><p class="text-xs text-slate-400">Kelola orang dan ruang kolaborasi.</p></div>
                        <button data-open-dialog="team-dialog" class="ml-auto grid h-11 w-11 place-items-center rounded-full bg-[#f2b84b] text-2xl font-bold text-[#153d36] shadow-md hover:scale-105 hover:bg-amber-400" aria-label="Buat tim">+</button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @forelse ($teams as $team)
                            <article data-dashboard-card data-search="{{ str($team->name.' '.$team->description)->lower() }}" class="group flex min-h-56 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                                <div class="flex items-start gap-3"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 font-extrabold text-emerald-700">{{ str($team->name)->substr(0, 2)->upper() }}</span><div class="min-w-0 flex-1"><a href="{{ route('teams.show', $team) }}" class="block truncate text-lg font-extrabold hover:text-emerald-700">{{ $team->name }}</a><p class="mt-1 text-xs font-bold {{ $team->isOwnedBy(auth()->user()) ? 'text-emerald-700' : 'text-violet-600' }}">{{ ucfirst($team->membershipRole(auth()->user())) }}</p></div>
                                    @if ($team->isOwnedBy(auth()->user()))<form method="POST" action="{{ route('teams.destroy', $team) }}" onsubmit="return confirm('Hapus tim beserta seluruh proyeknya?')">@csrf @method('DELETE')<button class="rounded-lg p-2 text-slate-300 opacity-0 hover:bg-rose-50 hover:text-rose-600 group-hover:opacity-100" aria-label="Hapus tim"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0V5h8v2m-9 0 1 12h8l1-12"/></svg></button></form>@endif
                                </div>
                                <p class="mt-4 line-clamp-2 text-sm leading-6 text-slate-500">{{ $team->description ?: 'Ruang kolaborasi untuk mengelola proyek dan pekerjaan tim.' }}</p>
                                <div class="mt-auto flex items-end justify-between gap-3 pt-6">
                                    <div><p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $team->members->count() + 1 }} anggota</p><div class="flex -space-x-2"><span title="{{ $team->owner->name }}" class="grid h-8 w-8 place-items-center rounded-full border-2 border-white bg-[#153d36] text-[9px] font-extrabold text-white">{{ str($team->owner->name)->substr(0, 2)->upper() }}</span>@foreach ($team->members->take(4) as $member)<span title="{{ $member->name }}" class="grid h-8 w-8 place-items-center rounded-full border-2 border-white {{ $avatarColors[($loop->index + 1) % count($avatarColors)] }} text-[9px] font-extrabold text-white">{{ str($member->name)->substr(0, 2)->upper() }}</span>@endforeach</div></div>
                                    <a href="{{ route('teams.show', $team) }}" class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-bold text-slate-500 hover:bg-emerald-50 hover:text-emerald-700">Kelola · {{ $team->boards->count() }}</a>
                                </div>
                            </article>
                        @empty
                            <button data-open-dialog="team-dialog" class="min-h-56 rounded-2xl border-2 border-dashed border-slate-300 bg-white/50 p-8 text-center hover:border-emerald-600 hover:bg-white"><span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-emerald-50 text-2xl text-emerald-700">+</span><p class="mt-4 font-extrabold">Buat tim pertama</p><p class="mt-1 text-sm text-slate-400">Mulai ruang kerja baru.</p></button>
                        @endforelse
                    </div>
                </section>

                <section class="mt-10">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-300 pb-4">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-white text-amber-600 shadow-sm"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H6a2 2 0 00-2 2v12h16V7a2 2 0 00-2-2h-3M9 5a3 3 0 016 0M9 5h6"/></svg></span>
                        <div><h2 class="text-xl font-extrabold">Proyek</h2><p class="text-xs text-slate-400">Semua papan kerja dari tim yang kamu ikuti.</p></div>
                        <button data-open-dialog="project-dialog" {{ $managedTeams->isEmpty() ? 'disabled' : '' }} class="ml-auto grid h-11 w-11 place-items-center rounded-full bg-[#f2b84b] text-2xl font-bold text-[#153d36] shadow-md hover:scale-105 hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-40" aria-label="Buat proyek">+</button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @forelse ($projects as $project)
                            @php $projectTeam = $teams->firstWhere('id', $project->team_id); @endphp
                            <article data-dashboard-card data-search="{{ str($project->name.' '.$project->description.' '.$projectTeam->name)->lower() }}" class="group relative flex min-h-60 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                                <div class="h-2 bg-gradient-to-r from-emerald-700 via-emerald-500 to-[#f2b84b]"></div>
                                <div class="flex flex-1 flex-col p-5"><div class="flex items-start gap-3"><div class="min-w-0 flex-1"><p class="text-[10px] font-extrabold uppercase tracking-[.15em] text-emerald-700">{{ $projectTeam->name }}</p><a href="{{ route('boards.show', $project) }}" class="mt-2 block truncate text-xl font-extrabold hover:text-emerald-700">{{ $project->name }}</a></div>@if ($projectTeam->canManageProjects(auth()->user()))<form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Hapus proyek ini?')">@csrf @method('DELETE')<button class="rounded-lg p-2 text-slate-300 opacity-0 hover:bg-rose-50 hover:text-rose-600 group-hover:opacity-100" aria-label="Hapus proyek"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0V5h8v2m-9 0 1 12h8l1-12"/></svg></button></form>@endif</div>
                                    <p class="mt-4 line-clamp-2 text-sm leading-6 text-slate-500">{{ $project->description ?: 'Papan proyek untuk mengatur tugas, diskusi, dan pengumuman.' }}</p>
                                    <div class="mt-auto flex items-end justify-between pt-6"><div class="flex -space-x-2"><span class="grid h-8 w-8 place-items-center rounded-full border-2 border-white bg-[#153d36] text-[9px] font-extrabold text-white">{{ str($projectTeam->owner->name)->substr(0, 2)->upper() }}</span>@foreach ($projectTeam->members->take(3) as $member)<span class="grid h-8 w-8 place-items-center rounded-full border-2 border-white {{ $avatarColors[($loop->index + 2) % count($avatarColors)] }} text-[9px] font-extrabold text-white">{{ str($member->name)->substr(0, 2)->upper() }}</span>@endforeach</div><a href="{{ route('boards.show', $project) }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100">Buka <span>→</span></a></div>
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-300 bg-white/50 p-10 text-center"><p class="font-extrabold">Belum ada proyek</p><p class="mt-1 text-sm text-slate-400">Buat tim lalu tambahkan proyek pertamamu.</p></div>
                        @endforelse
                    </div>
                </section>
            </div>
        </main>
    </div>

    <dialog id="team-dialog" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-3xl bg-white p-0 shadow-2xl backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm"><form method="POST" action="{{ route('teams.store') }}" class="p-6">@csrf<div class="flex items-start justify-between"><div><p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Tim baru</p><h2 class="mt-2 text-2xl font-extrabold">Buat ruang kolaborasi</h2></div><button type="button" data-close-dialog class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-xl">×</button></div><div class="mt-6 grid gap-4"><label class="grid gap-2 text-sm font-bold">Nama tim<input name="name" required maxlength="100" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" placeholder="Contoh: Tim Produk"></label><label class="grid gap-2 text-sm font-bold">Deskripsi<textarea name="description" maxlength="1000" rows="3" class="resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" placeholder="Apa fokus tim ini?"></textarea></label><button class="rounded-xl bg-[#153d36] px-5 py-3 font-bold text-white">Buat tim</button></div></form></dialog>

    <dialog id="project-dialog" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-3xl bg-white p-0 shadow-2xl backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm"><form method="POST" action="{{ route('projects.store') }}" class="p-6">@csrf<div class="flex items-start justify-between"><div><p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Proyek baru</p><h2 class="mt-2 text-2xl font-extrabold">Mulai papan kerja</h2></div><button type="button" data-close-dialog class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-xl">×</button></div><div class="mt-6 grid gap-4"><label class="grid gap-2 text-sm font-bold">Tim<select name="team_id" required class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100">@foreach ($ownedTeams as $team)<option value="{{ $team->id }}">{{ $team->name }}</option>@endforeach</select></label><label class="grid gap-2 text-sm font-bold">Nama proyek<input name="name" required maxlength="120" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" placeholder="Contoh: Website baru"></label><label class="grid gap-2 text-sm font-bold">Deskripsi<textarea name="description" maxlength="1000" rows="3" class="resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" placeholder="Tujuan singkat proyek..."></textarea></label><button class="rounded-xl bg-[#153d36] px-5 py-3 font-bold text-white">Buat proyek</button></div></form></dialog>

    <dialog id="member-dialog" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-3xl bg-white p-0 shadow-2xl backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm"><form id="member-form" method="POST" data-action-template="{{ url('/teams/__TEAM__/members') }}" class="p-6">@csrf<div class="flex items-start justify-between"><div><p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Anggota tim</p><h2 class="mt-2 text-2xl font-extrabold">Tambahkan rekan kerja</h2></div><button type="button" data-close-dialog class="grid h-9 w-9 place-items-center rounded-full bg-slate-100 text-xl">×</button></div><div class="mt-6 grid gap-4"><label class="grid gap-2 text-sm font-bold">Pilih tim<select id="member-team-select" required class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100">@foreach ($ownedTeams as $team)<option value="{{ $team->id }}">{{ $team->name }}</option>@endforeach</select></label><label class="grid gap-2 text-sm font-bold">Email pengguna<input name="email" type="email" required class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100" placeholder="rekan@email.com"></label><p class="text-xs leading-5 text-slate-400">Pengguna harus sudah memiliki akun RuangKerja.</p><button class="rounded-xl bg-[#153d36] px-5 py-3 font-bold text-white">Tambahkan anggota</button></div></form></dialog>

    @if ($errors->any())<div class="fixed bottom-5 right-5 z-[80] max-w-sm rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-xl">{{ $errors->first() }}</div>@endif
</x-layout>
