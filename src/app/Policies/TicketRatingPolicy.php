<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\TicketStatus;
use App\Models\User;

class TicketRatingPolicy
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
        if ($ticket->user_id !== $user->id) {
            return false;
        }

        $rateableIds = TicketStatus::whereIn('slug', ['resolved', 'closed'])->pluck('id');
        if (!$rateableIds->contains($ticket->status_id)) {
            return false;
        }

        return !$ticket->ratings()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, TicketRating $rating): bool
    {
        return $rating->user_id === $user->id;
    }
}
