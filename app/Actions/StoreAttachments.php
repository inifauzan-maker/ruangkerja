<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StoreAttachments
{
    public function __construct(public ScanUploadedFile $scanUploadedFile) {}

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function execute(Model $attachable, User $uploader, array $files): void
    {
        $storedPaths = [];

        try {
            foreach ($files as $file) {
                $scan = $this->scanUploadedFile->execute($file);
                $path = $file->store('attachments', 'local');
                $storedPaths[] = $path;

                $attachable->attachments()->create([
                    'uploader_id' => $uploader->id,
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $this->safeOriginalName($file),
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'size' => $file->getSize(),
                    'scan_status' => $scan['status'],
                    'scanned_at' => $scan['scanned_at'],
                ]);
            }
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);

            throw $exception;
        }
    }

    private function safeOriginalName(UploadedFile $file): string
    {
        $name = basename($file->getClientOriginalName());
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';

        return mb_substr(trim($name) ?: 'lampiran', 0, 255);
    }
}
