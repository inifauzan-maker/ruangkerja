<x-layout title="Masuk">
    <main class="grid min-h-screen lg:grid-cols-[1.08fr_.92fr]">
        <section class="relative hidden overflow-hidden bg-[#123A70] p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-red-600/40"></div>
            <div class="absolute -bottom-32 -left-24 h-96 w-96 rounded-full border-[56px] border-[#F5C542]/15"></div>
            <a href="{{ route('login') }}" class="relative flex items-center gap-3 text-xl font-extrabold">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#F5C542] text-xs text-[#123A70]">VM</span>
                Ruang Kerja _ Villa Merah
            </a>
            <div class="relative max-w-xl">
                <span class="mb-6 inline-flex rounded-full bg-red-700 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/10">Kolaborasi tanpa ribet</span>
                <h1 class="text-5xl font-extrabold leading-tight">Kerja tim lebih jelas, dari ide sampai selesai.</h1>
                <p class="mt-6 max-w-lg text-lg leading-8 text-blue-50/75">Atur proyek, pindahkan tugas, dan lihat progres tim dalam satu ruang kerja yang tenang.</p>
            </div>
            <p class="relative text-sm text-blue-100/60">Dibuat untuk tim Indonesia yang bergerak cepat.</p>
        </section>

        <section class="flex items-center justify-center bg-white px-6 py-12">
            <div class="w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-12 flex items-center gap-3 text-lg font-extrabold lg:hidden">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#F5C542] text-xs text-[#123A70]">VM</span>
                    Ruang Kerja _ Villa Merah
                </a>
                <p class="text-sm font-bold uppercase tracking-[.2em] text-red-700">Selamat datang</p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight">Masuk ke ruang kerja</h2>
                <p class="mt-3 text-slate-500">Lanjutkan pekerjaan yang paling penting hari ini.</p>

                <form method="POST" action="{{ route('login.store') }}" class="mt-9 grid gap-5">
                    @csrf
                    <label class="grid gap-2 text-sm font-semibold">
                        Email
                        <input name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                    <div class="grid gap-2 text-sm font-semibold">
                        <label for="password">Kata sandi</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 pr-12 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <button type="button" data-password-toggle="password" aria-label="Tampilkan kata sandi" aria-pressed="false" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-500 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg data-password-visible-icon xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12s-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg data-password-hidden-icon xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.9 5.4A10.7 10.7 0 0 1 12 5.25c6.25 0 9.75 6.75 9.75 6.75a17 17 0 0 1-3.2 3.9M6.2 6.2C3.6 8 2.25 12 2.25 12s3.5 6.75 9.75 6.75c1.4 0 2.65-.35 3.75-.9" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    @if ($errors->any())
                        <p class="rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $errors->first() }}</p>
                    @endif
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 accent-blue-700">
                        Ingat saya
                    </label>
                    <button class="rounded-xl bg-[#123A70] px-5 py-3.5 font-bold text-white shadow-lg shadow-blue-950/15 transition hover:-translate-y-0.5 hover:bg-[#1D4E89]">Masuk</button>
                </form>
                @if(config('sso.enabled'))
                    <a href="{{ route('sso.login') }}" class="mt-5 block rounded-xl border border-blue-200 bg-blue-50 px-5 py-3.5 text-center font-bold text-[#123A70] transition hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100">Masuk dengan akun SIVMI</a>
                @endif
                @unless(config('sso.enabled'))
                    <p class="mt-7 text-center text-sm text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-red-700 hover:underline">Buat ruang kerja</a></p>
                @endunless
            </div>
        </section>
    </main>
</x-layout>
