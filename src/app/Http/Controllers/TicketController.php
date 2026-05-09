<?php

namespace App\Http\Controllers;

use App\Filters\TicketFilter;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Requests\TicketUpdateRequest;
use App\Services\Tickets\TicketService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $user    = $request->user();
        $perPage = min($request->integer('per_page', 15), 100);

        $tickets = TicketFilter::apply($request, Ticket::query())
            ->with(['user', 'assignedUser', 'category', 'priority', 'status', 'tags'])
            ->when(!$user->isAdmin() && !$user->isAgent(), fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Tickets/Index', [
            'tickets' => TicketResource::collection($tickets),
            'filters' => [
                'search'   => $request->input('search', ''),
                'status'   => $request->input('status', ''),
                'priority' => $request->input('priority', ''),
                'mine'     => $request->boolean('mine'),
                'overdue'  => $request->boolean('overdue'),
            ],
        ]);
    }

    public function store(TicketStoreRequest $request)
    {
        $this->authorize('create', Ticket::class);

        $ticket = $this->ticketService->create($request->validated(), $request->user()->id);

        return TicketResource::make($ticket)->response()->setStatusCode(201);
    }

    public function show(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorize('view', $ticket);

        $user = $request->user();

        $ticket->load([
            'user',
            'assignedUser',
            'category',
            'priority',
            'status',
            'sla',
            'tags',
            'messages' => fn($q) => $q
                ->with(['user', 'attachments'])
                ->when(
                    !$user->isAdmin() && !$user->isAgent(),
                    fn($q) => $q->where('is_internal', false)
                ),
            'attachments',
            'history.user',
            'ratings',
        ]);

        return TicketResource::make($ticket);
    }

    public function update(TicketUpdateRequest $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorize('update', $ticket);

        $ticket = $this->ticketService->update($ticket, $request->validated(), $request->user()->id);

        return TicketResource::make($ticket);
    }

    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorize('delete', $ticket);

        $this->ticketService->delete($ticket, $request->user()->id);

        return response()->json(['message' => 'Ticket eliminado correctamente']);
    }
}
