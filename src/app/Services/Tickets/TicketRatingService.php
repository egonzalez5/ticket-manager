<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\TicketRating;

class TicketRatingService
{
    public function rate(Ticket $ticket, array $data, int $userId): TicketRating
    {
        return TicketRating::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $userId,
            'rating'    => $data['rating'],
            'comment'   => $data['comment'] ?? null,
        ]);
    }

    public function delete(TicketRating $rating): void
    {
        $rating->delete();
    }
}
