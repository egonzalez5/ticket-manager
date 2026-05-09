<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Sla;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Requests\TicketUpdateRequest;
use App\Services\Tickets\TicketSlaService;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketSlaService $slaService
    ) {}

    // LISTAR
    public function index()
    {
        $tickets = Ticket::with([
            'user',
            'assignedUser',
            'category',
            'priority',
            'status',
            'tags'
        ])->latest()->get();

        return response()->json($tickets);
    }

    // CREAR
    public function store(TicketStoreRequest $request)
    {
        $validated = $request->validated();

        // SLA (si te lo pasan desde UI/API)
        $sla = !empty($validated['sla_id']) ? Sla::find($validated['sla_id']) : null;
        $dueDate = $this->slaService->calculateDueDate($sla);

        $ticket = DB::transaction(function () use ($request, $validated, $sla, $dueDate) {
            $ticket = Ticket::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'user_id' => $request->user()->id,
                'category_id' => $validated['category_id'],
                'priority_id' => $validated['priority_id'],
                'status_id' => 1, // Open
                'sla_id' => $sla?->id,
                'due_date' => $dueDate,
            ]);

            // TAGS
            if (!empty($validated['tags'])) {
                $ticket->tags()->sync($validated['tags']);
            }

            return $ticket;
        });

        return response()->json($ticket, 201);
    }

    // VER DETALLE
    public function show($id)
    {
        $ticket = Ticket::with([
            'user',
            'assignedUser',
            'messages.user',
            'attachments',
            'tags',
            'history'
        ])->findOrFail($id);

        return response()->json($ticket);
    }

    // ACTUALIZAR
    public function update(TicketUpdateRequest $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validated();

        // TAGS (opcional)
        $tags = $validated['tags'] ?? null;
        unset($validated['tags']);

        // SLA + due_date: si la UI/API envía `sla_id` actualizamos el cálculo.
        if (array_key_exists('sla_id', $validated)) {
            $sla = !empty($validated['sla_id']) ? Sla::find($validated['sla_id']) : null;
            $validated['due_date'] = $this->slaService->calculateDueDate(
                $sla,
                $ticket->created_at
            );
        }

        $ticket->update($validated);

        if (!is_null($tags)) {
            $ticket->tags()->sync($tags ?? []);
        }

        return response()->json($ticket);
    }

    // ELIMINAR
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket eliminado correctamente'
        ]);
    }
}