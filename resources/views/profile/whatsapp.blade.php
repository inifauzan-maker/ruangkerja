<x-layout title="Koneksi WhatsApp">
    <div class="min-h-dvh bg-[#f1f2f0]">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex min-h-16 max-w-7xl items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('profile.show') }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50" aria-label="Kembali ke profil">←</a>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-600 font-extrabold text-white">W</span>
                <div class="min-w-0">
                    <h1 class="truncate font-extrabold">Koneksi WhatsApp</h1>
                    <p class="truncate text-xs text-slate-400">Notifikasi melalui Fonnte</p>
                </div>
                <a href="{{ route('dashboard') }}" class="ml-auto rounded-xl bg-[#123A70] px-4 py-2.5 text-sm font-bold text-white">Beranda</a>
            </div>
        </header>

        @if (session('status'))
            <div data-toast class="fixed right-5 top-20 z-[70] rounded-xl bg-[#123A70] px-4 py-3 text-sm font-semibold text-white shadow-xl">✓ {{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="fixed bottom-5 right-5 z-[80] max-w-md rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-xl">{{ $errors->first() }}</div>
        @endif

        <main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[minmax(0,1fr)_23rem] lg:px-8 lg:py-8">
            <section class="min-w-0 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-700">Pengaturan penerima</p>
                        <h2 class="mt-2 text-2xl font-extrabold">Hubungkan nomor WhatsApp</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">API key Fonnte dikelola secara global oleh administrator.</p>
                    </div>
                    @if ($connection?->is_active && $connection->hasNotificationConsent())
                        <span class="w-fit rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-extrabold text-emerald-700">● Koneksi aktif</span>
                    @elseif ($connection)
                        <span class="w-fit rounded-full bg-amber-100 px-3 py-1.5 text-xs font-extrabold text-amber-700">● Koneksi nonaktif</span>
                    @else
                        <span class="w-fit rounded-full bg-slate-100 px-3 py-1.5 text-xs font-extrabold text-slate-500">Belum terhubung</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('profile.whatsapp.update') }}" class="mt-7 grid gap-6">
                    @csrf
                    @method('PUT')

                    <label class="grid gap-2 text-sm font-bold">
                        Nomor WhatsApp penerima
                        <input name="recipient_phone" type="tel" required maxlength="16" value="{{ old('recipient_phone', $connection?->recipient_phone) }}" placeholder="+628123456789" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        <span class="text-xs font-normal leading-5 text-slate-400">Gunakan kode negara, misalnya +62. Nomor ini menerima notifikasi akun Anda.</span>
                    </label>

                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="font-extrabold">Preferensi notifikasi</h3>
                        <p class="mt-1 text-xs leading-5 text-slate-400">Aktivitas yang Anda lakukan sendiri tidak dikirim kembali.</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ([
                                'notify_task_created' => ['Tugas baru', 'Saat anggota membuat tugas'],
                                'notify_task_updated' => ['Perubahan tugas', 'Edit atau perpindahan status'],
                                'notify_chat_messages' => ['Pesan grup', 'Pesan baru di chat proyek'],
                                'notify_announcements' => ['Pengumuman', 'Pengumuman proyek baru'],
                                'notify_due_reminders' => ['Pengingat tenggat', 'H-1 dan hari jatuh tempo'],
                            ] as $field => [$label, $description])
                                <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 bg-white p-4">
                                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $connection?->getAttribute($field) ?? ($field !== 'notify_chat_messages'))) class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span><span class="block text-sm font-extrabold">{{ $label }}</span><span class="mt-1 block text-xs leading-5 text-slate-400">{{ $description }}</span></span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input type="checkbox" name="quiet_hours_enabled" value="1" @checked(old('quiet_hours_enabled', $connection?->quiet_hours_enabled ?? true)) class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span><span class="block font-extrabold">Jam tenang</span><span class="mt-1 block text-xs leading-5 text-slate-400">Pesan dijadwalkan setelah jam tenang berakhir.</span></span>
                        </label>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            <label class="grid gap-2 text-sm font-bold">Zona waktu
                                <select name="timezone" class="rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                                    @foreach (['Asia/Jakarta' => 'WIB - Jakarta', 'Asia/Makassar' => 'WITA - Makassar', 'Asia/Jayapura' => 'WIT - Jayapura', 'UTC' => 'UTC'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('timezone', $connection?->timezone ?? 'Asia/Jakarta') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="grid gap-2 text-sm font-bold">Mulai
                                <input name="quiet_hours_start" type="time" required value="{{ old('quiet_hours_start', $connection?->quiet_hours_start ?? '21:00') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                            </label>
                            <label class="grid gap-2 text-sm font-bold">Selesai
                                <input name="quiet_hours_end" type="time" required value="{{ old('quiet_hours_end', $connection?->quiet_hours_end ?? '07:00') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                            </label>
                        </div>
                    </section>

                    @if (! $connection?->hasNotificationConsent())
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4">
                            <input type="checkbox" name="consent_whatsapp" value="1" @checked(old('consent_whatsapp')) class="mt-1 h-4 w-4 rounded border-sky-300 text-sky-600 focus:ring-sky-500">
                            <span>
                                <span class="block text-sm font-extrabold text-sky-900">Saya setuju menerima notifikasi WhatsApp</span>
                                <span class="mt-1 block text-xs leading-5 text-sky-700/75">Ruang Kerja _ Villa Merah boleh mengirim pemberitahuan tugas, mention, pengumuman, dan tenggat ke nomor di atas. Persetujuan dapat dicabut kapan saja.</span>
                            </span>
                        </label>
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600">
                            <p class="font-extrabold text-slate-800">Persetujuan tercatat</p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">Diberikan {{ $connection->consented_at->timezone($connection->timezone)->format('d M Y, H:i') }}. Nonaktifkan pengiriman untuk mencabut persetujuan.</p>
                        </div>
                    @endif

                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $connection?->is_active ?? true)) class="mt-1 h-4 w-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
                        <span><span class="block text-sm font-extrabold text-emerald-900">Aktifkan pengiriman WhatsApp</span><span class="mt-1 block text-xs leading-5 text-emerald-700/70">Nonaktifkan untuk berhenti menerima notifikasi.</span></span>
                    </label>

                    <button class="w-fit rounded-xl bg-[#123A70] px-5 py-3 text-sm font-bold text-white">Simpan koneksi</button>
                </form>

                @if ($connection)
                    <div class="mt-5 flex flex-wrap gap-3 border-t border-slate-100 pt-5">
                        <form method="POST" action="{{ route('profile.whatsapp.test') }}">@csrf
                            <button class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white">Kirim pesan uji</button>
                        </form>
                        <form method="POST" action="{{ route('profile.whatsapp.destroy') }}" onsubmit="return confirm('Hapus nomor dan pengaturan WhatsApp Anda?')">@csrf @method('DELETE')
                            <button class="rounded-xl px-5 py-3 text-sm font-bold text-rose-600 hover:bg-rose-50">Putuskan koneksi</button>
                        </form>
                    </div>
                @endif
            </section>

            <aside class="grid content-start gap-6">
                <section class="rounded-3xl bg-[#123A70] p-6 text-white shadow-xl">
                    <p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-200/70">Status Fonnte</p>
                    @if ($connection)
                        <dl class="mt-5 grid gap-4 text-sm">
                            <div><dt class="text-white/45">Tes terakhir</dt><dd class="mt-1 font-bold">{{ $connection->last_tested_at?->diffForHumans() ?? 'Belum pernah' }}</dd></div>
                            <div><dt class="text-white/45">Pesan terakhir</dt><dd class="mt-1 font-bold">{{ $connection->last_sent_at?->diffForHumans() ?? 'Belum pernah' }}</dd></div>
                        </dl>
                        <a href="{{ route('profile.whatsapp.history') }}" class="mt-5 inline-flex rounded-xl bg-white/10 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-white/15">Lihat riwayat pengiriman</a>
                        @if ($connection->last_error_at)
                            <div class="mt-5 rounded-2xl bg-rose-500/15 p-4 text-xs leading-5 text-rose-100"><p class="font-extrabold">Kesalahan terakhir</p><p class="mt-1 break-words">{{ $connection->last_error_message }}</p></div>
                        @endif
                    @else
                        <p class="mt-4 text-sm leading-6 text-white/60">Simpan nomor WhatsApp terlebih dahulu untuk melihat status.</p>
                    @endif
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-extrabold uppercase tracking-[.15em] text-emerald-700">Cara mengaktifkan</p>
                    <ol class="mt-4 grid gap-3 text-sm leading-6 text-slate-600">
                        <li><strong>1.</strong> Masukkan nomor WhatsApp penerima.</li>
                        <li><strong>2.</strong> Pilih jenis notifikasi yang ingin diterima.</li>
                        <li><strong>3.</strong> Berikan persetujuan dan aktifkan pengiriman.</li>
                        <li><strong>4.</strong> Simpan pengaturan lalu kirim pesan uji.</li>
                    </ol>
                    <p class="mt-4 text-xs leading-5 text-slate-400">Kredensial layanan dikelola oleh administrator Ruang Kerja _ Villa Merah.</p>
                </section>
            </aside>
        </main>
    </div>
</x-layout>
