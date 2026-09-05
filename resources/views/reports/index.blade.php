<x-layout title="Laporan">
    @php $maxWorkload = max(1, $report['workload']->max('active') ?? 1); @endphp
    <div class="min-h-dvh bg-[#f4f5f3]">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex min-h-16 max-w-7xl flex-wrap items-center gap-3 px-4 py-3 sm:px-6">
                <a href="{{ route('dashboard') }}" class="grid h-10 w-10 place-items-center rounded-xl bg-[#F5C542] text-xs font-extrabold text-[#123A70]">VM</a>
                <div class="min-w-0 flex-1"><p class="text-xs font-bold text-emerald-700">Dashboard analitik</p><h1 class="text-lg font-extrabold">Laporan tim dan proyek</h1></div>
                <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500">Beranda</a>
                <x-current-user-avatar class="h-10 w-10 text-xs" />
            </div>
        </header>

        <main class="mx-auto max-w-7xl space-y-6 px-4 py-7 sm:px-6">
            <section class="flex flex-wrap items-end gap-3">
                <form method="GET" action="{{ route('reports.index') }}" class="grid flex-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_160px_auto]">
                    <label class="grid gap-1.5 text-xs font-bold text-slate-500">Proyek<select name="board" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm"><option value="">Semua proyek</option>@foreach ($report['boards'] as $reportBoard)<option value="{{ $reportBoard->id }}" @selected((int) $report['selected_board_id'] === $reportBoard->id)>{{ $reportBoard->name }}</option>@endforeach</select></label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-500">Periode aktivitas<select name="days" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm"><option value="7" @selected($report['days'] === 7)>7 hari</option><option value="30" @selected($report['days'] === 30)>30 hari</option><option value="90" @selected($report['days'] === 90)>90 hari</option></select></label>
                    <button class="self-end rounded-xl bg-[#123A70] px-5 py-2.5 text-sm font-bold text-white">Terapkan</button>
                </form>
                <div class="flex gap-2"><a href="{{ route('reports.pdf', ['board' => $report['selected_board_id'], 'days' => $report['days']]) }}" class="rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-bold text-rose-600">PDF</a><a href="{{ route('reports.excel', ['board' => $report['selected_board_id'], 'days' => $report['days']]) }}" class="rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-bold text-emerald-700">Excel</a></div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total tugas</p><p class="mt-3 text-3xl font-extrabold">{{ $report['metrics']['total'] }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Selesai</p><p class="mt-3 text-3xl font-extrabold text-emerald-700">{{ $report['metrics']['completed'] }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Dikerjakan</p><p class="mt-3 text-3xl font-extrabold text-amber-600">{{ $report['metrics']['in_progress'] }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Terlambat</p><p class="mt-3 text-3xl font-extrabold text-rose-600">{{ $report['metrics']['overdue'] }}</p></div>
            </section>

            <section class="grid gap-5 xl:grid-cols-[1.15fr_.85fr]">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><div><h2 class="font-extrabold">Beban kerja anggota</h2><p class="mt-1 text-xs text-slate-400">Berdasarkan tugas aktif yang ditugaskan.</p></div></div><div class="mt-5 grid gap-4">@forelse ($report['workload'] as $member)<div><div class="mb-2 flex items-center justify-between gap-3 text-sm"><span class="font-bold">{{ $member['name'] }}</span><span class="text-xs text-slate-400">{{ $member['active'] }} aktif · {{ $member['completed'] }} selesai @if ($member['overdue'])· <span class="text-rose-600">{{ $member['overdue'] }} terlambat</span>@endif</span></div><div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-600" style="width: {{ max(4, (int) round($member['active'] / $maxWorkload * 100)) }}%"></div></div></div>@empty<p class="text-sm text-slate-400">Belum ada anggota.</p>@endforelse</div></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-extrabold">Aktivitas per proyek</h2><p class="mt-1 text-xs text-slate-400">{{ $report['days'] }} hari terakhir.</p><div class="mt-5 grid gap-3">@forelse ($report['activity_by_project'] as $project)<div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-sm font-extrabold text-emerald-700">{{ $project['count'] }}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-bold">{{ $project['name'] }}</p><p class="text-[10px] text-slate-400">{{ $project['latest_at']?->diffForHumans() ?? 'Belum ada aktivitas' }}</p></div></div>@empty<p class="text-sm text-slate-400">Belum ada aktivitas.</p>@endforelse</div></div>
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-extrabold">Riwayat WhatsApp saya</h2><div class="mt-4 grid gap-2">@forelse ($report['whatsapp_logs']->take(10) as $log)<div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3"><span class="grid h-9 w-9 place-items-center rounded-lg bg-emerald-50">WA</span><div class="min-w-0 flex-1"><p class="truncate text-xs font-bold">{{ $log->subject }}</p><p class="text-[10px] text-slate-400">{{ $log->event_label }} · {{ $log->created_at->diffForHumans() }}</p></div><span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $log->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : ($log->status === 'failed' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">{{ $log->status }}</span></div>@empty<p class="rounded-xl bg-slate-50 p-5 text-sm text-slate-400">Belum ada notifikasi WhatsApp.</p>@endforelse</div></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-extrabold">Riwayat email</h2><div class="mt-4 grid gap-2">@forelse ($report['email_logs']->take(10) as $log)<div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3"><span class="grid h-9 w-9 place-items-center rounded-lg bg-sky-50">✉</span><div class="min-w-0 flex-1"><p class="truncate text-xs font-bold">{{ $log->subject }}</p><p class="truncate text-[10px] text-slate-400">{{ $log->recipient }} · {{ $log->created_at->diffForHumans() }}</p></div><span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $log->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $log->status }}</span></div>@empty<p class="rounded-xl bg-slate-50 p-5 text-sm text-slate-400">Belum ada email tercatat.</p>@endforelse</div></div>
            </section>
        </main>
    </div>
</x-layout>
