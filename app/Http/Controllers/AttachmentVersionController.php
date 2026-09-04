<?php

namespace App\Http\Controllers;

use App\Actions\ScanUploadedFile;
use App\Http\Requests\StoreAttachmentVersionRequest;
use App\Models\Announcement;
use App\Models\Attachment;
use App\Models\Board;
use App\Models\BoardMessage;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class AttachmentVersionController extends Controller
{
    public function store(
        StoreAttachmentVersionRequest $request,
        Attachment $attachment,
        ScanUploadedFile $scanUploadedFile,
    ): RedirectResponse {
        $board = $this->resolveBoard($attachment);
        abort_unless($board->belongsToUser($request->user()), 404);

        $file = $request->file('attachment');
        if (strtolower($file->getClientOriginalExtension()) !== strtolower(pathinfo($attachment->original_name, PATHINFO_EXTENSION))) {
            throw ValidationException::withMessages([
                'attachment' => 'Ekstensi versi baru harus sama dengan file sebelumnya.',
            ]);
        }

        $scan = $scanUploadedFile->execute($file);
        $path = $file->store('attachments', 'local');

        try {
            $newAttachment = DB::transaction(function () use ($attachment, $request, $file, $path, $scan): Attachment {
                $rootId = $attachment->familyRootId();
                $family = Attachment::query()
                    ->where('id', $rootId)
                    ->orWhere('root_attachment_id', $rootId);

                $nextVersion = ((int) (clone $family)->lockForUpdate()->max('version')) + 1;
                (clone $family)->update(['is_current' => false]);

                return $attachment->attachable->allAttachments()->create([
                    'root_attachment_id' => $rootId,
                    'uploader_id' => $request->user()->id,
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $this->safeOriginalName($file->getClientOriginalName()),
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'size' => $file->getSize(),
                    'version' => $nextVersion,
                    'is_current' => true,
                    'scan_status' => $scan['status'],
                    'scanned_at' => $scan['scanned_at'],
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        return redirect()->route('attachments.preview', $newAttachment)->with('status', 'Versi file baru berhasil diunggah.');
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename($name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';

        return mb_substr(trim($name) ?: 'lampiran', 0, 255);
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
