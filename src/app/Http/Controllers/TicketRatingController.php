<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketRatingResource;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Http\Requests\StoreTicketRatingRequest;
use App\Services\Tickets\TicketRatingService;
use Illuminate\Http\Request;

class TicketRatingController extends Controller
{
    public function __construct(
        private readonly TicketRatingService $service
    ) {}

    public function store(StoreTicketRatingRequest $request, Ticket $ticket)
    {
        $this->authorize('create', [TicketRating::class, $ticket]);

        $rating = $this->service->rate($ticket, $request->validated(), $request->user()->id);

        return TicketRatingResource::make($rating)->response()->setStatusCode(201);
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        $rating = $ticket->ratings()
                         ->where('user_id', $request->user()->id)
                         ->firstOrFail();

        $this->authorize('delete', $rating);

        $this->service->delete($rating);

        return response()->json(['message' => 'Calificación eliminada correctamente']);
    }
}
