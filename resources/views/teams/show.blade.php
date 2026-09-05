<x-layout :title="$team->name.' · Pengaturan Tim'">
    @php
        $role = $team->membershipRole(auth()->user());
        $isOwner = $role === 'owner';
        $canManageMembers = $team->canManageMembers(auth()->user());
        $canManageProjects = $team->canManageProjects(auth()->user());
    @endphp

    <div class="min-h-dvh bg-[#f1f2f0] text-slate-900">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex min-h-16 max-w-7xl items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50" aria-label="Kembali">←</a>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#F5C542] text-xs font-extrabold text-[#123A70]">{{ str($team->name)->substr(0, 2)->upper() }}</span>
                <div class="min-w-0"><h1 class="truncate font-extrabold">{{ $team->name }}</h1><p class="text-xs font-semibold text-slate-400">Pengaturan tim · {{ ucfirst($role) }}</p></div>
                <a href="{{ route('dashboard') }}" class="ml-auto rounded-xl bg-[#123A70] px-4 py-2.5 text-sm font-bold text-white">Beranda</a>
            </div>
        </header>

        @if (session('status'))
            <div data-toast class="fixed right-5 top-20 z-[70] rounded-xl bg-[#123A70] px-4 py-3 text-sm font-semibold text-white shadow-xl">✓ {{ session('status') }}</div>
        @endif

        <main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:px-8 lg:py-8">
            <div class="grid min-w-0 gap-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-700">Identitas tim</p><h2 class="mt-2 text-2xl font-extrabold">Informasi umum</h2></div><span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">{{ ucfirst($role) }}</span></div>

                    @if ($isOwner)
                        <form method="POST" action="{{ route('teams.update', $team) }}" class="mt-6 grid gap-4">@csrf @method('PATCH')
                            <label class="grid gap-2 text-sm font-bold">Nama tim<input name="name" value="{{ old('name', $team->name) }}" required maxlength="100" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100"></label>
                            <label class="grid gap-2 text-sm font-bold">Deskripsi<textarea name="description" rows="3" maxlength="1000" class="resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100">{{ old('description', $team->description) }}</textarea></label>
                            <button class="w-fit rounded-xl bg-[#123A70] px-5 py-3 text-sm font-bold text-white">Simpan perubahan</button>
                        </form>
                    @else
                        <p class="mt-5 text-sm leading-7 text-slate-500">{{ $team->description ?: 'Belum ada deskripsi tim.' }}</p>
                    @endif
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-700">Proyek</p><h2 class="mt-2 text-2xl font-extrabold">Papan kerja tim</h2></div><span class="text-sm font-bold text-slate-400">{{ $team->boards->count() }} proyek</span></div>

                    @if ($canManageProjects)
                        <details class="mt-6 rounded-2xl border border-dashed border-emerald-300 bg-emerald-50/50 p-4">
                            <summary class="cursor-pointer list-none font-bold text-emerald-800">+ Buat proyek baru</summary>
                            <form method="POST" action="{{ route('projects.store') }}" class="mt-4 grid gap-3">@csrf<input type="hidden" name="team_id" value="{{ $team->id }}"><input name="name" required maxlength="120" placeholder="Nama proyek" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-600"><textarea name="description" rows="2" maxlength="1000" placeholder="Deskripsi singkat" class="resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-600"></textarea><button class="w-fit rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white">Buat proyek</button></form>
                        </details>
                    @endif

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @forelse ($team->boards as $project)
                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-3"><div class="min-w-0"><a href="{{ route('boards.show', $project) }}" class="block truncate text-lg font-extrabold hover:text-blue-700">{{ $project->name }}</a><p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $project->description ?: 'Belum ada deskripsi.' }}</p></div><a href="{{ route('boards.show', $project) }}" class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-emerald-700 shadow-sm">Buka</a></div>
                                @if ($canManageProjects)
                                    <details class="mt-4 border-t border-slate-200 pt-3"><summary class="cursor-pointer text-xs font-bold text-slate-500">Edit proyek</summary><form method="POST" action="{{ route('projects.update', $project) }}" class="mt-3 grid gap-2">@csrf @method('PATCH')<input name="name" value="{{ $project->name }}" required maxlength="120" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"><textarea name="description" rows="2" maxlength="1000" class="resize-none rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">{{ $project->description }}</textarea><div class="flex gap-2"><button class="rounded-lg bg-[#123A70] px-3 py-2 text-xs font-bold text-white">Simpan</button></form><form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Hapus proyek ini?')">@csrf @method('DELETE')<button class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-bold text-rose-600">Hapus</button></form></div></details>
                                @endif
                            </article>
                        @empty
                            <p class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">Belum ada proyek.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="grid content-start gap-6">
                @if ($canManageMembers)
                    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-700">Undangan</p><h2 class="mt-2 text-xl font-extrabold">Tambah rekan</h2>
                        <form method="POST" action="{{ route('teams.invitations.store', $team) }}" class="mt-5 grid gap-3">@csrf
                            <label class="grid gap-2 text-xs font-bold">Email<input name="email" type="email" required placeholder="rekan@email.com" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-600"></label>
                            @if ($isOwner)
                                <label class="grid gap-2 text-xs font-bold">Role<select name="role" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"><option value="member">Member</option><option value="admin">Admin</option></select></label>
                            @else
                                <input type="hidden" name="role" value="member">
                            @endif
                            <button class="rounded-xl bg-[#F5C542] px-4 py-3 text-sm font-extrabold text-[#123A70]">Kirim undangan</button>
                        </form>
                        @if ($team->invitations->isNotEmpty())
                            <div class="mt-5 grid gap-2 border-t border-slate-100 pt-4">@foreach ($team->invitations as $invitation)<div class="flex items-center gap-2 rounded-xl bg-slate-50 p-3"><div class="min-w-0 flex-1"><p class="truncate text-xs font-bold">{{ $invitation->email }}</p><p class="text-[10px] uppercase text-slate-400">{{ $invitation->role }} · menunggu</p></div><form method="POST" action="{{ route('team-invitations.destroy', $invitation) }}">@csrf @method('DELETE')<button class="text-xs font-bold text-rose-500">Batal</button></form></div>@endforeach</div>
                        @endif
                    </section>
                @endif

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><div><p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-700">Anggota</p><h2 class="mt-2 text-xl font-extrabold">{{ $team->members->count() + 1 }} orang</h2></div></div>
                    <div class="mt-5 grid gap-3">
                        <div class="flex items-center gap-3 rounded-xl bg-emerald-50 p-3"><span class="grid h-9 w-9 place-items-center rounded-full bg-[#123A70] text-[10px] font-extrabold text-white">{{ str($team->owner->name)->substr(0, 2)->upper() }}</span><div class="min-w-0"><p class="truncate text-sm font-bold">{{ $team->owner->name }}</p><p class="truncate text-xs text-slate-500">Owner · {{ $team->owner->email }}</p></div></div>
                        @foreach ($team->members as $member)
                            <div class="rounded-xl border border-slate-100 p-3"><div class="flex items-center gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-200 text-[10px] font-extrabold text-slate-600">{{ str($member->name)->substr(0, 2)->upper() }}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-bold">{{ $member->name }}</p><p class="truncate text-xs text-slate-400">{{ $member->email }}</p></div></div>
                                @if ($canManageMembers)
                                    <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3">@if ($isOwner)<form method="POST" action="{{ route('teams.members.update', [$team, $member]) }}" class="flex flex-1 gap-2">@csrf @method('PATCH')<select name="role" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs"><option value="member" @selected($member->pivot->role === 'member')>Member</option><option value="admin" @selected($member->pivot->role === 'admin')>Admin</option></select><button class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-bold">Ubah</button></form>@else<span class="flex-1 text-xs font-bold capitalize text-slate-500">{{ $member->pivot->role }}</span>@endif
                                        @if ($isOwner || $member->pivot->role !== 'admin')<form method="POST" action="{{ route('teams.members.destroy', [$team, $member]) }}" onsubmit="return confirm('Keluarkan anggota ini?')">@csrf @method('DELETE')<button class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-bold text-rose-600">Keluarkan</button></form>@endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>

                @if ($isOwner)
                    <section class="rounded-3xl border border-rose-200 bg-rose-50 p-5"><h2 class="font-extrabold text-rose-700">Zona berbahaya</h2><p class="mt-2 text-xs leading-5 text-rose-600">Menghapus tim juga menghapus seluruh proyek dan tugasnya.</p><form method="POST" action="{{ route('teams.destroy', $team) }}" class="mt-4" onsubmit="return confirm('Hapus tim beserta seluruh datanya?')">@csrf @method('DELETE')<button class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white">Hapus tim</button></form></section>
                @endif
            </aside>
        </main>

        @if ($errors->any())<div class="fixed bottom-5 right-5 z-[80] max-w-sm rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-xl">{{ $errors->first() }}</div>@endif
    </div>
</x-layout>
