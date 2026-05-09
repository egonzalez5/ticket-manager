<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;

class AttachmentPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function create(User $user, Ticket $ticket): bool
    {
        if ($user->isAgent()) return true;
        return $ticket->user_id === $user->id;
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        if ($user->isAgent()) return true;

        return Ticket::where('id', $attachment->ticket_id)
                     ->where('user_id', $user->id)
                     ->exists();
    }

    public function download(User $user, Attachment $attachment): bool
    {
        if ($user->isAgent()) return true;

        if ($attachment->message_id !== null) {
            $isInternal = TicketMessage::where('id', $attachment->message_id)
                                       ->value('is_internal');
            if ($isInternal) return false;
        }

        return Ticket::where('id', $attachment->ticket_id)
                     ->where('user_id', $user->id)
                     ->exists();
    }
}
