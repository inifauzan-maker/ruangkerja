@props(['attachments'])

@if ($attachments->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'mt-3 flex flex-wrap gap-2']) }}>
        @foreach ($attachments as $attachment)
            <div class="group/file inline-flex max-w-full items-center gap-1 rounded-lg border border-slate-200 bg-white/90 p-1 shadow-sm">
                <a href="{{ route('attachments.preview', $attachment) }}" class="inline-flex min-w-0 items-center gap-2 rounded-md px-2 py-1 text-left hover:bg-blue-50" title="Preview {{ $attachment->original_name }}">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-md bg-emerald-50 text-xs">{{ str_starts_with($attachment->mime_type, 'image/') ? '🖼️' : '📎' }}</span>
                    <span class="min-w-0"><span class="block max-w-40 truncate text-[11px] font-bold text-slate-700">{{ $attachment->original_name }}</span><span class="block text-[9px] text-slate-400">{{ $attachment->formattedSize() }} · v{{ $attachment->version }}</span></span>
                </a>
                <a href="{{ route('attachments.show', $attachment) }}" class="rounded-md px-1.5 py-1 text-xs text-slate-400 hover:bg-blue-50 hover:text-blue-700" title="Download">↓</a>
                @if ($attachment->uploader_id === auth()->id())
                    <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" onsubmit="return confirm('Hapus lampiran ini?')">@csrf @method('DELETE')<button class="rounded-md px-1.5 py-1 text-xs text-slate-300 hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus lampiran">×</button></form>
                @endif
            </div>
        @endforeach
    </div>
@endif
