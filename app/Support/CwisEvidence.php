<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use League\Flysystem\FilesystemException;

class CwisEvidence
{
    public const UPLOAD_ACCEPT = '.pdf,.doc,.docx';

    public static function uploadRules(): array
    {
        return ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'];
    }

    public static function store(UploadedFile $file, string $directory, string $disk, string $errorKey): string
    {
        $reason = 'Storage driver returned false.';
        try {
            $path = $file->store($directory, $disk);
            if ($path !== false) {
                return $path;
            }
        } catch (FilesystemException $exception) {
            $reason = $exception->getMessage();
        }

        Log::warning('Unable to store CWIS evidence document.', [
            'disk' => $disk, 'directory' => $directory, 'reason' => $reason,
        ]);
        throw ValidationException::withMessages([
            $errorKey => __('Evidence could not be saved to server storage. Ask the administrator to check upload directory permissions, available disk space, and persistent storage, then retry.'),
        ]);
    }

    public static function response(string $disk, string $path, ?string $name, bool $preview)
    {
        $storage = Storage::disk($disk);
        abort_unless($storage->exists($path), 404);
        $headers = ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'];
        if ($preview && in_array($storage->mimeType($path), ['application/pdf', 'image/jpeg', 'image/png'], true)) {
            return $storage->response($path, $name, $headers, 'inline');
        }
        return $storage->download($path, $name, $headers);
    }
}
