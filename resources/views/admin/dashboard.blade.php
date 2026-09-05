<x-layout title="Pusat Superadmin">
    <div class="min-h-dvh bg-[#f5f5f5]">
        <div id="admin-sidebar-backdrop" class="fixed inset-0 z-40 hidden bg-slate-950/45 backdrop-blur-sm lg:hidden"></div>

        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-[260px] -translate-x-full flex-col overflow-hidden bg-[#0B2447] text-white shadow-2xl transition-[width,transform] duration-300 lg:translate-x-0">
            <div class="flex h-16 shrink-0 items-center border-b border-white/10 px-5">
                <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#F5C542] text-sm font-extrabold text-[#0B2447] shadow-lg shadow-blue-950/30">VM</span>
                    <span data-admin-sidebar-label class="min-w-0">
                        <span class="block truncate text-sm font-extrabold tracking-tight">Ruang Kerja _ Villa Merah</span>
                        <span class="block truncate text-[9px] font-bold uppercase tracking-[.2em] text-slate-400">Superadmin</span>
                    </span>
                </a>
                <button id="admin-sidebar-close" type="button" class="ml-auto grid h-9 w-9 place-items-center rounded-lg text-slate-400 hover:bg-white/10 hover:text-white lg:hidden" aria-label="Tutup navigasi">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-5">
                <p data-admin-sidebar-label class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[.18em] text-slate-500">Menu utama</p>
                <div class="grid gap-1">
                    <a href="#overview" class="flex h-11 items-center gap-3 rounded-lg bg-[#C62828] px-3 text-sm font-bold text-white shadow-sm shadow-red-950/20">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        <span data-admin-sidebar-label>Ringkasan</span>
                    </a>
                    <a href="#users" class="flex h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span data-admin-sidebar-label>Manajemen pengguna</span>
                    </a>
                    <a href="#audit" class="flex h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                        <span data-admin-sidebar-label>Audit aktivitas</span>
                    </a>
                </div>

                <p data-admin-sidebar-label class="px-3 pb-2 pt-7 text-[10px] font-bold uppercase tracking-[.18em] text-slate-500">Ruang kerja</p>
                <div class="grid gap-1">
                    <a href="{{ route('dashboard') }}" class="flex h-11 items-center gap-3 rounded-lg px-3 text-sm font-semibold text-slate-300 hover:bg-white/10 hover:text-white">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10M9 20v-6h6v6"/></svg>
                        <span data-admin-sidebar-label>Kembali ke beranda</span>
                    </a>
                </div>
            </nav>

            <div class="shrink-0 border-t border-white/10 p-3">
                <div class="flex items-center gap-3 rounded-lg bg-white/5 p-2">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-amber-400 text-xs font-extrabold text-[#0B2447]">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    <div data-admin-sidebar-label class="min-w-0 flex-1">
                        <p class="truncate text-xs font-bold text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[10px] text-slate-400">Superadmin</p>
                    </div>
                </div>
            </div>
        </aside>

        <div id="admin-main-shell" class="min-h-dvh transition-[padding] duration-300 lg:pl-[260px]">
            <header class="sticky top-0 z-30 flex h-16 items-center border-b border-slate-200 bg-white/95 px-4 shadow-sm backdrop-blur sm:px-6">
                <button id="admin-mobile-toggle" type="button" class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-600 hover:border-blue-300 hover:bg-blue-50 hover:text-[#2563EB] lg:hidden" aria-label="Buka navigasi" aria-expanded="false">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <button id="admin-collapse-toggle" type="button" class="hidden h-9 w-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-[#2563EB] lg:grid" aria-label="Perkecil sidebar" aria-expanded="true">
                    <svg id="admin-collapse-icon" class="h-5 w-5 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </button>

                <div class="ml-3 flex min-w-0 items-center gap-2 text-sm">
                    <span class="hidden text-slate-400 sm:inline">Pusat Superadmin</span>
                    <span class="hidden text-slate-300 sm:inline">/</span>
                    <span class="truncate font-semibold text-slate-700">Dashboard</span>
                </div>

                <div class="ml-auto flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="hidden h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-600 hover:border-blue-300 hover:text-[#2563EB] sm:flex">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                        Beranda
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex h-9 items-center gap-2 rounded-lg px-3 text-xs font-bold text-slate-600 hover:bg-rose-50 hover:text-rose-600">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/></svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-[#2563EB] text-xs font-extrabold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                </div>
            </header>

            <main class="mx-auto grid max-w-[1680px] gap-5 p-4 sm:p-6">
                @if (session('status'))
                    <div data-toast class="fixed right-4 top-20 z-[70] flex max-w-md items-start gap-3 rounded-lg border border-emerald-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-xl">
                        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-500 text-xs font-bold text-white">✓</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="flex items-start gap-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-rose-500 text-xs text-white">!</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <section id="overview" class="scroll-mt-20">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold text-[#2563EB]">RINGKASAN PLATFORM</p>
                            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}</h1>
                            <p class="mt-1 text-sm text-slate-500">Pantau kondisi platform dan kelola akses pengguna dari satu tempat.</p>
                        </div>
                        <p class="text-xs font-semibold text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                        @foreach ([
                            ['Pengguna', $stats['users'], 'bg-blue-50 text-[#2563EB]', 'M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M17 11a3 3 0 1 0 0-6 M21 21v-2a4 4 0 0 0-3-3.87'],
                            ['Pengguna aktif', $stats['active_users'], 'bg-emerald-50 text-emerald-600', 'M20 6 9 17l-5-5'],
                            ['Superadmin', $stats['superadmins'], 'bg-amber-50 text-amber-600', 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z M9 12l2 2 4-4'],
                            ['Tim', $stats['teams'], 'bg-violet-50 text-violet-600', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 M22 21v-2a4 4 0 0 0-3-3.87'],
                            ['Proyek', $stats['projects'], 'bg-cyan-50 text-cyan-600', 'M3 7h6l2 2h10v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z M3 7V5a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v2'],
                            ['Tugas', $stats['tasks'], 'bg-rose-50 text-rose-600', 'M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
                        ] as [$label, $value, $color, $path])
                            <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
                                        <p class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900">{{ number_format($value) }}</p>
                                    </div>
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg {{ $color }}">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="{{ $path }}"/></svg>
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="users" class="scroll-mt-20 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 p-4 sm:p-5">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="h-5 w-1 rounded-full bg-[#2563EB]"></span>
                                    <h2 class="text-lg font-extrabold text-slate-900">Manajemen pengguna</h2>
                                </div>
                                <p class="mt-1 pl-3 text-xs text-slate-500">Atur status akun dan hak akses global.</p>
                            </div>
                            <form method="GET" action="{{ route('admin.dashboard') }}#users" class="grid gap-2 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_150px_150px_auto]">
                                <label class="relative sm:col-span-2 xl:col-span-1">
                                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                                    <input name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="Cari nama atau email" class="h-10 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm outline-none placeholder:text-slate-400 focus:border-[#2563EB] focus:ring-2 focus:ring-blue-100">
                                </label>
                                <select name="role" class="h-10 rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#2563EB] focus:ring-2 focus:ring-blue-100">
                                    <option value="">Semua role</option>
                                    <option value="user" @selected($filters['role'] === 'user')>User</option>
                                    <option value="superadmin" @selected($filters['role'] === 'superadmin')>Superadmin</option>
                                </select>
                                <select name="status" class="h-10 rounded-md border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#2563EB] focus:ring-2 focus:ring-blue-100">
                                    <option value="">Semua status</option>
                                    <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                                    <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                                </select>
                                <div class="flex gap-2">
                                    <button class="h-10 flex-1 rounded-md bg-[#2563EB] px-4 text-sm font-bold text-white shadow-sm hover:bg-blue-600">Filter</button>
                                    @if ($filters['q'] || $filters['role'] || $filters['status'])
                                        <a href="{{ route('admin.dashboard') }}#users" class="grid h-10 w-10 place-items-center rounded-md border border-slate-300 text-slate-500 hover:border-[#2563EB] hover:text-[#2563EB]" aria-label="Hapus filter">×</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="hidden overflow-x-auto lg:block">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-200 bg-slate-50/80 text-xs font-bold text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Pengguna</th>
                                    <th class="px-5 py-3">Ruang kerja</th>
                                    <th class="px-5 py-3">Terdaftar</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3 text-right">Akses global</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($users as $user)
                                    <tr class="group hover:bg-blue-50/30">
                                        <td class="px-5 py-4">
                                            <div class="flex min-w-56 items-center gap-3">
                                                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-xs font-extrabold text-white shadow-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <p class="truncate font-bold text-slate-900">{{ $user->name }}</p>
                                                        @if (auth()->user()->is($user))
                                                            <span class="rounded bg-blue-50 px-1.5 py-0.5 text-[9px] font-bold text-[#2563EB]">ANDA</span>
                                                        @endif
                                                    </div>
                                                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-slate-700">{{ $user->owned_teams_count }} tim</p>
                                            <p class="mt-0.5 text-xs text-slate-400">{{ $user->joined_teams_count }} keanggotaan</p>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                                        <td class="px-5 py-4">
                                            @if ($user->is_active)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif</span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex min-w-[310px] items-center justify-end gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="global_role" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold outline-none focus:border-[#2563EB]">
                                                    <option value="user" @selected($user->global_role === 'user')>User</option>
                                                    <option value="superadmin" @selected($user->global_role === 'superadmin')>Superadmin</option>
                                                </select>
                                                <input type="hidden" name="is_active" value="0">
                                                <label class="flex h-9 cursor-pointer items-center gap-2 rounded-md border border-slate-300 px-3 text-xs font-semibold">
                                                    <input type="checkbox" name="is_active" value="1" @checked($user->is_active) class="h-4 w-4 rounded border-slate-300 text-[#2563EB] focus:ring-blue-500">
                                                    Aktif
                                                </label>
                                                <button class="h-9 rounded-md border border-[#2563EB] px-3 text-xs font-bold text-[#2563EB] hover:bg-[#2563EB] hover:text-white">Simpan</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-5 py-14 text-center text-sm text-slate-400">Tidak ada pengguna yang cocok dengan filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="divide-y divide-slate-100 lg:hidden">
                        @forelse ($users as $user)
                            <article class="grid gap-4 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-xs font-extrabold text-white">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate text-sm font-bold text-slate-900">{{ $user->name }}</p>
                                            @if (auth()->user()->is($user))
                                                <span class="rounded bg-blue-50 px-1.5 py-0.5 text-[9px] font-bold text-[#2563EB]">ANDA</span>
                                            @endif
                                        </div>
                                        <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                    <span class="h-2 w-2 shrink-0 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 rounded-md bg-slate-50 p-3 text-xs">
                                    <div><p class="text-slate-400">Ruang kerja</p><p class="mt-1 font-bold text-slate-700">{{ $user->owned_teams_count }} tim · {{ $user->joined_teams_count }} anggota</p></div>
                                    <div><p class="text-slate-400">Terdaftar</p><p class="mt-1 font-bold text-slate-700">{{ $user->created_at->format('d M Y') }}</p></div>
                                </div>
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid grid-cols-2 gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="global_role" class="h-10 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold outline-none focus:border-[#2563EB]">
                                        <option value="user" @selected($user->global_role === 'user')>User</option>
                                        <option value="superadmin" @selected($user->global_role === 'superadmin')>Superadmin</option>
                                    </select>
                                    <input type="hidden" name="is_active" value="0">
                                    <label class="flex h-10 cursor-pointer items-center justify-center gap-2 rounded-md border border-slate-300 text-xs font-semibold">
                                        <input type="checkbox" name="is_active" value="1" @checked($user->is_active) class="h-4 w-4 rounded border-slate-300 text-[#2563EB] focus:ring-blue-500">
                                        Akun aktif
                                    </label>
                                    <button class="col-span-2 h-10 rounded-md bg-[#2563EB] text-xs font-bold text-white hover:bg-blue-600">Simpan perubahan</button>
                                </form>
                            </article>
                        @empty
                            <p class="p-10 text-center text-sm text-slate-400">Tidak ada pengguna yang cocok dengan filter.</p>
                        @endforelse
                    </div>

                    @if ($users->hasPages())
                        <div class="border-t border-slate-200 px-4 py-4 sm:px-5">{{ $users->links() }}</div>
                    @endif
                </section>

                <section id="audit" class="scroll-mt-20 rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4 sm:px-5">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-5 w-1 rounded-full bg-[#2563EB]"></span>
                                <h2 class="text-lg font-extrabold text-slate-900">Aktivitas superadmin</h2>
                            </div>
                            <p class="mt-1 pl-3 text-xs text-slate-500">Jejak perubahan akses global terbaru.</p>
                        </div>
                        <span class="hidden rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 sm:inline">15 TERBARU</span>
                    </div>
                    <div class="divide-y divide-slate-100 px-4 sm:px-5">
                        @forelse ($recentAudits as $audit)
                            <article class="flex gap-3 py-4">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-50 text-[#2563EB]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm leading-6 text-slate-700">
                                        <strong class="text-slate-900">{{ $audit->actor?->name ?? 'Perintah sistem' }}</strong>
                                        memperbarui
                                        <strong class="text-slate-900">{{ $audit->targetUser?->name ?? 'pengguna yang dihapus' }}</strong>
                                    </p>
                                    <p class="truncate text-xs text-slate-400">{{ $audit->action }} · {{ $audit->targetUser?->email }}</p>
                                </div>
                                <time class="hidden shrink-0 text-xs font-semibold text-slate-400 sm:block">{{ $audit->created_at->diffForHumans() }}</time>
                            </article>
                        @empty
                            <div class="grid place-items-center gap-2 py-12 text-center">
                                <span class="grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-400">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                </span>
                                <p class="text-sm text-slate-400">Belum ada aktivitas superadmin.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-layout>
