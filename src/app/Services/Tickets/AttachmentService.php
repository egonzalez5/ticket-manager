<?php

namespace App\Services\Tickets;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    public function store(Ticket $ticket, UploadedFile $file, ?TicketMessage $message = null): Attachment
    {
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . ($extension ? ".{$extension}" : '');
        $disk      = config('filesystems.default');
        $path      = Storage::disk($disk)->putFileAs("attachments/{$ticket->id}", $file, $filename);

        return Attachment::create([
            'ticket_id'  => $ticket->id,
            'message_id' => $message?->id,
            'file_name'  => $file->getClientOriginalName(),
            'file_path'  => $path,
            'mime_type'  => $file->getMimeType(),
            'file_size'  => $file->getSize(),
        ]);
    }

    public function delete(Attachment $attachment): void
    {
        $disk = config('filesystems.default');

        if (Storage::disk($disk)->exists($attachment->file_path)) {
            Storage::disk($disk)->delete($attachment->file_path);
        }

        $attachment->delete();
    }

    /** Limpia archivos físicos ya escritos (rollback parcial en creación de ticket). */
    public function cleanup(array $paths): void
    {
        $disk = config('filesystems.default');

        foreach ($paths as $path) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }
}
