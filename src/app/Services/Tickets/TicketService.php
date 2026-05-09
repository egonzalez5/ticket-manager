<?php

namespace App\Services\Tickets;

use App\Models\Sla;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        private readonly TicketSlaService $slaService
    ) {}

    public function create(array $data, int $creatorId): Ticket
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $sla = !empty($data['sla_id']) ? Sla::find($data['sla_id']) : null;

            $ticket = Ticket::create([
                'title'       => $data['title'],
                'description' => $data['description'],
                'user_id'     => $creatorId,
                'category_id' => $data['category_id'],
                'priority_id' => $data['priority_id'],
                'status_id'   => $this->resolveOpenStatus()->id,
                'sla_id'      => $sla?->id,
                'due_date'    => $this->slaService->calculateDueDate($sla),
            ]);

            $this->syncTags($ticket, $data['tags'] ?? null);
            $this->recordHistory($ticket, $creatorId, 'created');

            return $ticket;
        });
    }

    public function update(Ticket $ticket, array $data, int $updaterId): Ticket
    {
        return DB::transaction(function () use ($ticket, $data, $updaterId) {
            $tags = $data['tags'] ?? null;
            unset($data['tags']);

            if (array_key_exists('sla_id', $data)) {
                $sla = !empty($data['sla_id']) ? Sla::find($data['sla_id']) : null;
                $data['due_date'] = $this->slaService->calculateDueDate($sla, $ticket->created_at);
            }

            $originalStatusId = $ticket->status_id;

            $ticket->update($data);

            $this->recordHistoryForUpdate($ticket, $updaterId, $originalStatusId);
            $this->syncTags($ticket, $tags);

            return $ticket->fresh();
        });
    }

    public function delete(Ticket $ticket, int $deleterId): void
    {
        DB::transaction(function () use ($ticket, $deleterId) {
            $this->recordHistory($ticket, $deleterId, 'deleted');
            $ticket->delete();
        });
    }

    private function resolveOpenStatus(): TicketStatus
    {
        return TicketStatus::where('slug', 'open')->firstOrFail();
    }

    private function syncTags(Ticket $ticket, ?array $tags): void
    {
        if (!is_null($tags)) {
            $ticket->tags()->sync($tags);
        }
    }

    private function recordHistory(Ticket $ticket, int $userId, string $action, ?string $description = null): void
    {
        TicketHistory::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $userId,
            'action'      => $action,
            'description' => $description,
        ]);
    }

    private function recordHistoryForUpdate(Ticket $ticket, int $updaterId, ?int $originalStatusId): void
    {
        if ($ticket->wasChanged('status_id')) {
            $oldStatus = TicketStatus::find($originalStatusId)?->name ?? 'N/A';
            $newStatus = TicketStatus::find($ticket->status_id)?->name ?? 'N/A';
            $this->recordHistory($ticket, $updaterId, 'status_changed', "{$oldStatus} → {$newStatus}");
        }

        if ($ticket->wasChanged('assigned_to')) {
            $assignee = $ticket->assigned_to
                ? User::find($ticket->assigned_to)?->name ?? 'N/A'
                : 'Sin asignar';
            $this->recordHistory($ticket, $updaterId, 'assigned', "Asignado a: {$assignee}");
        }

        if ($ticket->wasChanged() && !$ticket->wasChanged('status_id') && !$ticket->wasChanged('assigned_to')) {
            $this->recordHistory($ticket, $updaterId, 'updated');
        }
    }
}
