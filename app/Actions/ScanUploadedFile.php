<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class ScanUploadedFile
{
    /**
     * @return array{status: string, scanned_at: Carbon|null}
     */
    public function execute(UploadedFile $file): array
    {
        if (! config('attachments.antivirus.enabled')) {
            return ['status' => 'unscanned', 'scanned_at' => null];
        }

        $process = new Process([
            (string) config('attachments.antivirus.command', 'clamscan'),
            '--no-summary',
            $file->getRealPath(),
        ]);
        $process->setTimeout((float) config('attachments.antivirus.timeout', 30));
        $process->run();

        if ($process->getExitCode() === 0) {
            return ['status' => 'clean', 'scanned_at' => now()];
        }

        if ($process->getExitCode() === 1) {
            throw ValidationException::withMessages([
                'attachments' => 'File ditolak karena terdeteksi mengandung malware.',
            ]);
        }

        if (config('attachments.antivirus.fail_closed', true)) {
            throw ValidationException::withMessages([
                'attachments' => 'File belum dapat dipindai. Silakan coba kembali.',
            ]);
        }

        return ['status' => 'scan_failed', 'scanned_at' => now()];
    }
}
