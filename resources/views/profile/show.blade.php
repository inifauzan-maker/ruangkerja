<x-layout title="Profil Saya">
    <div class="min-h-dvh bg-[#f1f2f0]">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex min-h-16 max-w-7xl items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50" aria-label="Kembali">←</a>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#F5C542] text-xs font-extrabold text-[#123A70]">VM</span>
                <div><h1 class="font-extrabold">Profil Saya</h1><p class="text-xs text-slate-400">Kelola identitas dan keamanan akun</p></div>
                <a href="{{ route('dashboard') }}" class="ml-auto rounded-xl bg-[#123A70] px-4 py-2.5 text-sm font-bold text-white">Beranda</a>
            </div>
        </header>

        @if (session('status'))
            <div data-toast class="fixed right-5 top-20 z-[70] rounded-xl bg-[#123A70] px-4 py-3 text-sm font-semibold text-white shadow-xl">✓ {{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="fixed bottom-5 right-5 z-[80] max-w-sm rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-xl">{{ $errors->first() }}</div>
        @endif

        <main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[20rem_minmax(0,1fr)] lg:px-8 lg:py-8">
            <aside class="grid content-start gap-6">
                <section class="overflow-hidden rounded-3xl bg-[#123A70] text-white shadow-xl">
                    <div class="bg-[radial-gradient(circle_at_top_right,rgba(242,184,75,.35),transparent_45%)] p-6 text-center">
                        <x-current-user-avatar :user="$user" class="mx-auto h-28 w-28 border-4 border-white/15 text-3xl shadow-lg" />
                        <h2 class="mt-5 text-xl font-extrabold">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-blue-100/60">{{ $user->job_title ?: 'Anggota Ruang Kerja _ Villa Merah' }}</p>
                        <p class="mt-3 break-all text-xs text-blue-100/45">{{ $user->email }}</p>
                    </div>
                    <div class="grid grid-cols-3 border-t border-white/10 text-center">
                        <div class="p-4"><p class="text-xl font-extrabold text-[#F5C542]">{{ $user->teams_count + $user->joined_teams_count }}</p><p class="mt-1 text-[10px] uppercase tracking-wider text-white/45">Tim</p></div>
                        <div class="border-x border-white/10 p-4"><p class="text-xl font-extrabold text-[#F5C542]">{{ $projectsCount }}</p><p class="mt-1 text-[10px] uppercase tracking-wider text-white/45">Proyek</p></div>
                        <div class="p-4"><p class="text-xl font-extrabold text-[#F5C542]">{{ $user->tasks_count }}</p><p class="mt-1 text-[10px] uppercase tracking-wider text-white/45">Tugas</p></div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-700">Foto profil</p>
                    <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="mt-4 grid gap-3">@csrf
                        <input type="file" name="avatar" required accept=".jpg,.jpeg,.png,.webp" class="min-w-0 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:font-bold file:text-blue-700">
                        <p class="text-[11px] leading-5 text-slate-400">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                        <button class="rounded-xl bg-[#F5C542] px-4 py-2.5 text-sm font-extrabold text-[#123A70]">Upload foto</button>
                    </form>
                    @if ($user->avatar_path)
                        <form method="POST" action="{{ route('profile.avatar.destroy') }}" class="mt-2">@csrf @method('DELETE')<button class="w-full rounded-xl px-4 py-2.5 text-sm font-bold text-rose-600 hover:bg-rose-50">Hapus foto</button></form>
                    @endif
                </section>
            </aside>

            <div class="grid min-w-0 content-start gap-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div><p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-700">Informasi pribadi</p><h2 class="mt-2 text-2xl font-extrabold">Tentang Anda</h2><p class="mt-2 text-sm text-slate-400">Informasi ini membantu tim mengenali peran dan cara menghubungi Anda.</p></div>
                    <form method="POST" action="{{ route('profile.update') }}" class="mt-7 grid gap-5">@csrf @method('PATCH')
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="grid gap-2 text-sm font-bold">Nama lengkap<input name="name" value="{{ old('name', $user->name) }}" required maxlength="100" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100"></label>
                            <label class="grid gap-2 text-sm font-bold">Email<input name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100"></label>
                            <label class="grid gap-2 text-sm font-bold">Jabatan <span class="font-normal text-slate-400">(opsional)</span><input name="job_title" value="{{ old('job_title', $user->job_title) }}" maxlength="100" placeholder="Contoh: Product Designer" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100"></label>
                            <label class="grid gap-2 text-sm font-bold">Nomor telepon <span class="font-normal text-slate-400">(opsional)</span><input name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30" placeholder="+62 812 3456 7890" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100"></label>
                        </div>
                        <label class="grid gap-2 text-sm font-bold">Bio <span class="font-normal text-slate-400">(opsional)</span><textarea name="bio" rows="4" maxlength="1000" placeholder="Ceritakan sedikit tentang pekerjaan dan fokus Anda..." class="resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">{{ old('bio', $user->bio) }}</textarea></label>
                        <button class="w-fit rounded-xl bg-[#123A70] px-5 py-3 text-sm font-bold text-white">Simpan profil</button>
                    </form>
                </section>

                <section class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm sm:p-7">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-emerald-600 text-2xl text-white">◉</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-700">Integrasi</p>
                            <h2 class="mt-2 text-2xl font-extrabold">Notifikasi WhatsApp</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Hubungkan API key Fonnte milik Anda untuk menerima pemberitahuan aktivitas proyek.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            @if ($user->whatsappConnection?->is_active)
                                <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-extrabold text-emerald-700">Aktif</span>
                            @elseif ($user->whatsappConnection)
                                <span class="rounded-full bg-amber-100 px-3 py-1.5 text-xs font-extrabold text-amber-700">Nonaktif</span>
                            @endif
                            <a href="{{ route('profile.whatsapp.show') }}" class="rounded-xl bg-[#123A70] px-5 py-3 text-sm font-bold text-white">Kelola koneksi</a>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <div><p class="text-xs font-extrabold uppercase tracking-[.15em] text-amber-600">Keamanan akun</p><h2 class="mt-2 text-2xl font-extrabold">Ubah password</h2><p class="mt-2 text-sm text-slate-400">Gunakan password yang unik dan tidak digunakan pada layanan lain.</p></div>
                    <form method="POST" action="{{ route('profile.password.update') }}" class="mt-7 grid gap-5">@csrf @method('PATCH')
                        <label class="grid gap-2 text-sm font-bold">Password saat ini<input name="current_password" type="password" required autocomplete="current-password" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100"></label>
                        <div class="grid gap-5 sm:grid-cols-2"><label class="grid gap-2 text-sm font-bold">Password baru<input name="password" type="password" required autocomplete="new-password" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100"></label><label class="grid gap-2 text-sm font-bold">Konfirmasi password<input name="password_confirmation" type="password" required autocomplete="new-password" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100"></label></div>
                        <button class="w-fit rounded-xl bg-[#123A70] px-5 py-3 text-sm font-bold text-white">Perbarui password</button>
                    </form>
                </section>

                <p class="px-2 text-xs text-slate-400">Akun dibuat {{ $user->created_at->translatedFormat('d F Y') }} · Terakhir diperbarui {{ $user->updated_at->diffForHumans() }}</p>
            </div>
        </main>
    </div>
</x-layout>
