<?php

namespace App\Services\Tickets;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Notifications\NewTicketReplyNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketMessageService
{
    public function __construct(
        private readonly AttachmentService $attachmentService
    ) {}

    public function addMessage(Ticket $ticket, array $data, int $userId): TicketMessage
    {
        $message = DB::transaction(function () use ($ticket, $data, $userId) {
            $message = TicketMessage::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $userId,
                'message'     => $data['message'],
                'is_internal' => $data['is_internal'] ?? false,
            ]);

            foreach ($data['attachments'] ?? [] as $file) {
                $this->attachmentService->store($ticket, $file, $message);
            }

            return $message->load(['user', 'attachments']);
        });

        // Internal notes never trigger email notifications
        if (!($data['is_internal'] ?? false)) {
            $this->notifyReply($ticket, $message, $userId);
        }

        return $message;
    }

    public function deleteMessage(TicketMessage $message): void
    {
        DB::transaction(function () use ($message) {
            $message->load('attachments');

            foreach ($message->attachments as $attachment) {
                $this->attachmentService->delete($attachment);
            }

            $message->delete();
        });
    }

    public function addAttachmentToTicket(Ticket $ticket, UploadedFile $file): Attachment
    {
        return $this->attachmentService->store($ticket, $file);
    }

    public function deleteAttachment(Attachment $attachment): void
    {
        $this->attachmentService->delete($attachment);
    }

    // ── Notification helper ───────────────────────────────────────────────────

    private function notifyReply(Ticket $ticket, TicketMessage $message, int $senderId): void
    {
        try {
            $ticket->loadMissing(['user', 'assignedUser']);
            $sender = User::find($senderId);

            if (!$sender) return;

            // Staff replied → notify ticket creator
            // User replied → notify assigned agent
            $recipient = ($sender->isAdmin() || $sender->isAgent())
                ? $ticket->user
                : $ticket->assignedUser;

            // Never notify yourself
            if ($recipient && $recipient->id !== $senderId) {
                $recipient->notify(new NewTicketReplyNotification($ticket, $message));
            }
        } catch (\Throwable $e) {
            Log::error("NewTicketReplyNotification failed for ticket {$ticket->id}: {$e->getMessage()}");
        }
    }
}
