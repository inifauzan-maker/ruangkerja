<x-layout :title="$user->exists ? 'Edit pengguna' : 'Tambah pengguna'">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-4xl items-center justify-between gap-4 px-4 py-5 sm:px-6">
            <div>
                <p class="text-xs font-bold text-blue-600">PUSAT SUPERADMIN</p>
                <h1 class="mt-1 text-xl font-extrabold">{{ $user->exists ? 'Edit pengguna' : 'Tambah pengguna' }}</h1>
            </div>
            <a href="{{ route('admin.dashboard') }}#users" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">Kembali</a>
        </div>
    </header>
    <main class="mx-auto grid max-w-4xl gap-5 px-4 py-6 sm:px-6">
        @if (session('status'))
            <p role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <p class="font-bold">Periksa kembali data pengguna:</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="grid gap-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @csrf
            @if ($user->exists)
                @method('PATCH')
            @endif
            <div>
                <h2 class="text-lg font-extrabold">Informasi pengguna</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola identitas, keamanan, dan akses akun.</p>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach (['name' => ['Nama lengkap', 'text', 100, true], 'email' => ['Email', 'email', 255, true], 'job_title' => ['Jabatan (opsional)', 'text', 100, false], 'phone' => ['Nomor telepon (opsional)', 'tel', 30, false]] as $field => [$label, $type, $maximum, $required])
                    <label class="grid gap-2 text-sm font-bold" for="{{ $field }}">{{ $label }}
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field, $user->{$field}) }}" maxlength="{{ $maximum }}" @required($required) class="w-full rounded-lg border border-slate-300 px-3 py-3 font-normal outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                    </label>
                @endforeach
            </div>
            <label class="grid gap-2 text-sm font-bold" for="bio">Bio (opsional)
                <textarea id="bio" name="bio" rows="3" maxlength="1000" class="w-full rounded-lg border border-slate-300 px-3 py-3 font-normal outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">{{ old('bio', $user->bio) }}</textarea>
            </label>
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-bold" for="password">{{ $user->exists ? 'Password baru (opsional)' : 'Password' }}
                    <input id="password" name="password" type="password" autocomplete="new-password" @required(! $user->exists) class="w-full rounded-lg border border-slate-300 px-3 py-3 font-normal outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                </label>
                <label class="grid gap-2 text-sm font-bold" for="password_confirmation">Konfirmasi password
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $user->exists) class="w-full rounded-lg border border-slate-300 px-3 py-3 font-normal outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                </label>
            </div>
            @if ($user->exists)
                <p class="text-xs text-slate-500">Kosongkan password untuk mempertahankan password lama. Perubahan email atau password mengakhiri sesi login pengguna.</p>
            @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="grid gap-2 text-sm font-bold" for="global_role">Role
                    <select id="global_role" name="global_role" required class="rounded-lg border border-slate-300 bg-white px-3 py-3 font-normal">
                        <option value="user" @selected(old('global_role', $user->global_role) === 'user')>User</option>
                        <option value="superadmin" @selected(old('global_role', $user->global_role) === 'superadmin')>Superadmin</option>
                    </select>
                </label>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-3 text-sm font-bold">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="h-4 w-4 rounded border-slate-300 text-blue-600">
                        Akun aktif
                    </label>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5">
                <button class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">{{ $user->exists ? 'Simpan perubahan' : 'Tambah pengguna' }}</button>
                <a href="{{ route('admin.dashboard') }}#users" class="rounded-lg border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</a>
            </div>
        </form>
        @if ($user->exists && ! auth()->user()->is($user))
            <section class="grid gap-3 rounded-xl border border-rose-200 bg-white p-5 sm:p-8">
                <h2 class="font-extrabold text-rose-700">Hapus pengguna</h2>
                <p class="text-sm text-slate-500">Penghapusan permanen hanya tersedia untuk akun tanpa kepemilikan tim, tugas, atau konten kerja. Gunakan status nonaktif untuk mempertahankan data akun yang sudah digunakan.</p>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus akun pengguna ini secara permanen? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg border border-rose-300 px-5 py-3 text-sm font-bold text-rose-700 hover:bg-rose-50">Hapus pengguna</button>
                </form>
            </section>
        @endif
    </main>
</x-layout>
