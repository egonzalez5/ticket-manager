<?php

namespace App\Services\Tickets;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketMessageService
{
    public function addMessage(Ticket $ticket, array $data, int $userId): TicketMessage
    {
        return DB::transaction(function () use ($ticket, $data, $userId) {
            $message = TicketMessage::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $userId,
                'message'     => $data['message'],
                'is_internal' => $data['is_internal'] ?? false,
            ]);

            foreach ($data['attachments'] ?? [] as $file) {
                $this->storeFile($ticket, $message, $file);
            }

            return $message->load(['user', 'attachments']);
        });
    }

    public function deleteMessage(TicketMessage $message): void
    {
        DB::transaction(function () use ($message) {
            $message->load('attachments');

            foreach ($message->attachments as $attachment) {
                $this->removeFile($attachment);
                $attachment->delete();
            }

            $message->delete();
        });
    }

    public function addAttachmentToTicket(Ticket $ticket, UploadedFile $file): Attachment
    {
        return $this->storeFile($ticket, null, $file);
    }

    public function deleteAttachment(Attachment $attachment): void
    {
        $this->removeFile($attachment);
        $attachment->delete();
    }

    private function storeFile(Ticket $ticket, ?TicketMessage $message, UploadedFile $file): Attachment
    {
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . ($extension ? ".{$extension}" : '');
        $path      = Storage::putFileAs("attachments/{$ticket->id}", $file, $filename);

        return Attachment::create([
            'ticket_id'  => $ticket->id,
            'message_id' => $message?->id,
            'file_name'  => $file->getClientOriginalName(),
            'file_path'  => $path,
            'mime_type'  => $file->getMimeType(),
            'file_size'  => $file->getSize(),
        ]);
    }

    private function removeFile(Attachment $attachment): void
    {
        if (Storage::exists($attachment->file_path)) {
            Storage::delete($attachment->file_path);
        }
    }
}
