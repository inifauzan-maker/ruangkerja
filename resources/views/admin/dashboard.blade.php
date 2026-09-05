<x-layout title="Superadmin">
    <div class="min-h-screen bg-[#f5f6f3]">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-[1600px] items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-amber-400 font-extrabold text-[#153d36]">SA</div>
                <div>
                    <p class="font-extrabold tracking-tight">Pusat Superadmin</p>
                    <p class="text-[10px] font-bold uppercase tracking-[.16em] text-slate-400">RuangKerja</p>
                </div>
                <a href="{{ route('dashboard') }}" class="ml-auto rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">Kembali ke beranda</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-xl bg-[#153d36] px-4 py-2.5 text-sm font-bold text-white">Keluar</button>
                </form>
            </div>
        </header>

        <main class="mx-auto grid max-w-[1600px] gap-6 px-4 py-6 sm:px-6 lg:px-8">
            <section>
                <p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-700">Ringkasan platform</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">Kelola RuangKerja</h1>
                <p class="mt-2 text-sm text-slate-500">Pantau pertumbuhan platform dan kelola akses global pengguna.</p>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                    @foreach ([
                        ['Pengguna', $stats['users'], 'bg-sky-50 text-sky-700'],
                        ['Pengguna aktif', $stats['active_users'], 'bg-emerald-50 text-emerald-700'],
                        ['Superadmin aktif', $stats['superadmins'], 'bg-amber-50 text-amber-700'],
                        ['Tim', $stats['teams'], 'bg-violet-50 text-violet-700'],
                        ['Proyek', $stats['projects'], 'bg-cyan-50 text-cyan-700'],
                        ['Tugas', $stats['tasks'], 'bg-rose-50 text-rose-700'],
                    ] as [$label, $value, $color])
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="inline-flex rounded-lg px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider {{ $color }}">{{ $label }}</div>
                            <p class="mt-3 text-3xl font-extrabold text-slate-950">{{ number_format($value) }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-800">{{ $errors->first() }}</div>
            @endif

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-700">Manajemen akses</p>
                            <h2 class="mt-2 text-xl font-extrabold">Pengguna</h2>
                        </div>
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="grid gap-2 sm:grid-cols-[minmax(220px,1fr)_160px_160px_auto]">
                            <input name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="Cari nama atau email" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100">
                            <select name="role" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-600">
                                <option value="">Semua role</option>
                                <option value="user" @selected($filters['role'] === 'user')>User</option>
                                <option value="superadmin" @selected($filters['role'] === 'superadmin')>Superadmin</option>
                            </select>
                            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-600">
                                <option value="">Semua status</option>
                                <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                                <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
                            </select>
                            <button class="rounded-xl bg-[#153d36] px-4 py-2.5 text-sm font-bold text-white">Filter</button>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-extrabold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Pengguna</th>
                                <th class="px-5 py-3">Ruang kerja</th>
                                <th class="px-5 py-3">Terdaftar</th>
                                <th class="px-5 py-3">Akses global</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($users as $user)
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <div class="flex min-w-60 items-center gap-3">
                                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[#153d36] text-xs font-extrabold text-white">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                            <div>
                                                <p class="font-extrabold text-slate-900">{{ $user->name }}</p>
                                                <p class="mt-0.5 text-xs text-slate-500">{{ $user->email }}</p>
                                                @if (auth()->user()->is($user))
                                                    <span class="mt-1 inline-flex rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-extrabold text-sky-700">AKUN ANDA</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        <p><strong class="text-slate-900">{{ $user->owned_teams_count }}</strong> dimiliki</p>
                                        <p class="mt-1 text-xs">{{ $user->joined_teams_count }} keanggotaan</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                                    <td class="px-5 py-4">
                                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex min-w-[330px] flex-wrap items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="global_role" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold outline-none focus:border-emerald-600">
                                                <option value="user" @selected($user->global_role === 'user')>User</option>
                                                <option value="superadmin" @selected($user->global_role === 'superadmin')>Superadmin</option>
                                            </select>
                                            <input type="hidden" name="is_active" value="0">
                                            <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold">
                                                <input type="checkbox" name="is_active" value="1" @checked($user->is_active) class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                Aktif
                                            </label>
                                            <button class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-800">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-12 text-center text-slate-400">Tidak ada pengguna yang cocok dengan filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">{{ $users->links() }}</div>
                @endif
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[.18em] text-emerald-700">Jejak keamanan</p>
                    <h2 class="mt-2 text-xl font-extrabold">Aktivitas superadmin terbaru</h2>
                </div>
                <div class="mt-5 grid gap-3">
                    @forelse ($recentAudits as $audit)
                        <article class="flex flex-col gap-2 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800">
                                    {{ $audit->actor?->name ?? 'Perintah sistem' }}
                                    <span class="font-normal text-slate-500">memperbarui</span>
                                    {{ $audit->targetUser?->name ?? 'pengguna yang dihapus' }}
                                </p>
                                <p class="mt-1 truncate text-xs text-slate-400">{{ $audit->action }} · {{ $audit->targetUser?->email }}</p>
                            </div>
                            <time class="whitespace-nowrap text-xs font-semibold text-slate-400">{{ $audit->created_at->diffForHumans() }}</time>
                        </article>
                    @empty
                        <p class="rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">Belum ada aktivitas superadmin.</p>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</x-layout>
