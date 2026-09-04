<?php

namespace App\Http\Controllers;

use App\Actions\StoreAttachments;
use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Announcement;
use App\Models\Attachment;
use App\Models\Board;
use App\Models\BoardMessage;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function store(
        StoreAttachmentRequest $request,
        Board $board,
        Task $task,
        StoreAttachments $storeAttachments,
    ): RedirectResponse {
        abort_unless($board->belongsToUser($request->user()), 404);
        abort_unless($task->list()->where('board_id', $board->id)->exists(), 404);

        $storeAttachments->execute($task, $request->user(), $request->file('attachments'));

        return back()->with('status', 'Lampiran ditambahkan ke tugas.');
    }

    public function preview(Request $request, Attachment $attachment): View
    {
        $board = $this->authorizeFileAccess($request, $attachment);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        $rootId = $attachment->familyRootId();
        $versions = Attachment::query()
            ->with('uploader:id,name')
            ->where('id', $rootId)
            ->orWhere('root_attachment_id', $rootId)
            ->orderByDesc('version')
            ->get();

        $textPreview = null;
        if (str_starts_with($attachment->mime_type, 'text/') && $attachment->size <= 1_048_576) {
            $textPreview = Storage::disk($attachment->disk)->get($attachment->path);
        }

        return view('attachments.preview', compact('attachment', 'board', 'versions', 'textPreview'));
    }

    public function inline(Request $request, Attachment $attachment): StreamedResponse
    {
        $this->authorizeFileAccess($request, $attachment);
        abort_unless($attachment->isInlinePreviewable(), 404);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'",
            ],
        );
    }

    public function show(Request $request, Attachment $attachment): StreamedResponse
    {
        $this->authorizeFileAccess($request, $attachment);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type, 'X-Content-Type-Options' => 'nosniff'],
        );
    }

    public function destroy(Request $request, Attachment $attachment): RedirectResponse
    {
        $board = $this->resolveBoard($attachment);
        $board->load('team.members');
        abort_unless($board->belongsToUser($request->user()), 404);
        abort_unless($attachment->uploader_id === $request->user()->id || $board->team->canManageProjects($request->user()), 404);
        abort_if($attachment->root_attachment_id === null && $attachment->versions()->exists(), 422, 'Versi awal tidak dapat dihapus selama riwayat versi masih ada.');

        $wasCurrent = $attachment->is_current;
        $rootId = $attachment->familyRootId();
        $attachment->delete();

        if ($wasCurrent) {
            Attachment::query()
                ->where('id', $rootId)
                ->orWhere('root_attachment_id', $rootId)
                ->orderByDesc('version')
                ->first()
                ?->update(['is_current' => true]);
        }

        return back()->with('status', 'Lampiran dihapus.');
    }

    private function authorizeFileAccess(Request $request, Attachment $attachment): Board
    {
        $board = $this->resolveBoard($attachment);
        abort_unless($board->belongsToUser($request->user()), 404);
        $board->load('team.members');

        $canAccess = match ($board->download_permission) {
            'managers' => $attachment->uploader_id === $request->user()->id
                || $board->team->canManageProjects($request->user()),
            'uploader' => $attachment->uploader_id === $request->user()->id
                || $board->team->isOwnedBy($request->user()),
            default => true,
        };
        abort_unless($canAccess, 404);

        return $board;
    }

    private function resolveBoard(Attachment $attachment): Board
    {
        $attachable = $attachment->attachable;

        $board = match (true) {
            $attachable instanceof Task => $attachable->list->board,
            $attachable instanceof BoardMessage => $attachable->board,
            $attachable instanceof Announcement => $attachable->board,
            default => null,
        };

        abort_unless($board instanceof Board, 404);

        return $board;
    }
}
