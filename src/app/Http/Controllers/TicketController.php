<?php

namespace App\Http\Controllers;

use App\Filters\TicketFilter;
use App\Http\Resources\TicketResource;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Sla;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketMessage;
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

    public function create()
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('Tickets/Create', [
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'priorities' => Priority::active()->orderBy('level', 'desc')->get(['id', 'name', 'level']),
            'tags'       => Tag::active()->orderBy('name')->get(['id', 'name']),
            'slas'       => Sla::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(TicketStoreRequest $request)
    {
        $this->authorize('create', Ticket::class);

        $ticket = $this->ticketService->create(
            $request->validated(),
            $request->user()->id,
            $request->file('attachments') ?? [],
        );

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket created successfully.');
    }

    public function show(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorize('view', $ticket);

        $user    = $request->user();
        $isStaff = $user->isAdmin() || $user->isAgent();

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
                ->when(!$user->can('internalNote', $ticket), fn($q) => $q->where('is_internal', false))
                ->oldest(),
            'attachments',
            'history.user',
            'ratings',
        ]);

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
            'can' => [
                'update'         => $user->can('update',         $ticket),
                'changeStatus'   => $user->can('changeStatus',   $ticket),
                'changePriority' => $user->can('changePriority', $ticket),
                'assign'         => $user->can('assign',          $ticket),
                'internalNote'   => $user->can('internalNote',   $ticket),
                'delete'         => $user->can('delete',          $ticket),
                'reply'          => $user->can('create', [TicketMessage::class, $ticket]),
            ],
        ]);
    }

    public function update(TicketUpdateRequest $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorize('update', $ticket);

        $user = $request->user();
        $data = $request->validated();

        // Strip fields the user is not permitted to modify — backend enforces regardless of UI
        if (!$user->can('changeStatus',   $ticket)) unset($data['status_id']);
        if (!$user->can('changePriority', $ticket)) unset($data['priority_id']);
        if (!$user->can('assign',          $ticket)) unset($data['assigned_to']);

        $this->ticketService->update($ticket, $data, $user->id);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorize('delete', $ticket);

        $this->ticketService->delete($ticket, $request->user()->id);

        return response()->json(['message' => 'Ticket eliminado correctamente']);
    }
}
