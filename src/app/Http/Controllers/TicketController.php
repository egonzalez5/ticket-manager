<?php

namespace App\Http\Controllers;

use App\Filters\TicketFilter;
use App\Http\Resources\TicketResource;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
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

        return redirect()->route('tickets.show', $ticket->id);
    }

    public function show(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorize('view', $ticket);

        $user    = $request->user();
        $isStaff = $user->isAdmin() || $user->isAgent();
        /* dd($ticket); */
        $ticket->load([
            'user',
            'assignedUser',
            'category',
            'priority',
            'status',
            'sla',
            'tags',
            'messages' => fn($q) => $q
                ->with(['user.role', 'attachments'])
                ->when(!$isStaff, fn($q) => $q->where('is_internal', false))
                ->oldest(),
            'attachments',
            'history.user',
            'ratings',
        ]);
        /* dd(TicketResource::make($ticket)->resolve()); */
        return Inertia::render('Tickets/Show', [
            'ticket'     => TicketResource::make($ticket)->resolve(),
            'statuses'   => TicketStatus::active()->get(['id', 'name', 'slug']),
            'priorities' => Priority::active()->get(['id', 'name', 'level']),
            'agents'     => $isStaff
                ? User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'agent']))
                    ->where('active', true)
                    ->orderBy('name')
                    ->get(['id', 'name'])
                : collect(),
            'canUpdate'  => $user->can('update', $ticket),
            'isStaff'    => $isStaff,
        ]);
    }

    public function update(TicketUpdateRequest $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorize('update', $ticket);

        $this->ticketService->update($ticket, $request->validated(), $request->user()->id);

        return redirect()->route('tickets.show', $ticket->id);
    }

    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorize('delete', $ticket);

        $this->ticketService->delete($ticket, $request->user()->id);

        return response()->json(['message' => 'Ticket eliminado correctamente']);
    }
}
