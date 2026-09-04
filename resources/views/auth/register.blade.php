<x-layout title="Daftar">
    <main class="grid min-h-screen lg:grid-cols-[.92fr_1.08fr]">
        <section class="flex items-center justify-center bg-white px-6 py-12">
            <div class="w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-10 flex items-center gap-3 text-lg font-extrabold">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#f2b84b] text-[#173f38]">R</span>
                    RuangKerja
                </a>
                <p class="text-sm font-bold uppercase tracking-[.2em] text-emerald-700">Mulai gratis</p>
                <h1 class="mt-3 text-3xl font-extrabold tracking-tight">Buat ruang kerja baru</h1>
                <p class="mt-3 text-slate-500">Papan dan empat list awal dibuat otomatis untukmu.</p>
                <form method="POST" action="{{ route('register.store') }}" class="mt-8 grid gap-4">
                    @csrf
                    <label class="grid gap-2 text-sm font-semibold">Nama lengkap
                        <input name="name" value="{{ old('name') }}" required autofocus class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </label>
                    <label class="grid gap-2 text-sm font-semibold">Email
                        <input name="email" type="email" value="{{ old('email') }}" required class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </label>
                    <label class="grid gap-2 text-sm font-semibold">Kata sandi
                        <input name="password" type="password" required class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </label>
                    <label class="grid gap-2 text-sm font-semibold">Ulangi kata sandi
                        <input name="password_confirmation" type="password" required class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    </label>
                    @if ($errors->any())
                        <p class="rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $errors->first() }}</p>
                    @endif
                    <button class="mt-2 rounded-xl bg-[#173f38] px-5 py-3.5 font-bold text-white hover:bg-[#205148]">Buat akun</button>
                </form>
                <p class="mt-6 text-center text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:underline">Masuk</a></p>
            </div>
        </section>
        <section class="relative hidden overflow-hidden bg-[#f2b84b] p-12 text-[#173f38] lg:flex lg:flex-col lg:justify-end">
            <div class="absolute left-16 top-16 grid w-[72%] grid-cols-2 gap-4 -rotate-2">
                @foreach (['Riset kebutuhan', 'Susun alur produk', 'Review desain', 'Rilis versi pertama'] as $index => $item)
                    <div class="rounded-2xl bg-white/85 p-5 shadow-xl shadow-amber-900/10 {{ $index % 2 ? 'translate-y-8' : '' }}">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Tugas {{ $index + 1 }}</span>
                        <p class="mt-3 font-extrabold">{{ $item }}</p>
                        <div class="mt-5 h-2 rounded-full bg-stone-100"><div class="h-2 rounded-full bg-emerald-700" style="width: {{ 35 + ($index * 17) }}%"></div></div>
                    </div>
                @endforeach
            </div>
            <div class="relative z-10 rounded-3xl bg-[#173f38] p-8 text-white shadow-2xl">
                <p class="text-2xl font-extrabold">Mulai rapi sejak hari pertama.</p>
                <p class="mt-3 text-emerald-50/70">Dari daftar tugas sederhana sampai alur kerja tim yang lengkap.</p>
            </div>
        </section>
    </main>
</x-layout>
