<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getData(User $user, bool $isStaff): array
    {
        // Fetch all status IDs once to avoid repeated queries per metric
        $statusIds = TicketStatus::pluck('id', 'slug');

        return [
            'kpis'          => $this->getKpis($user, $isStaff, $statusIds),
            'byStatus'      => $this->getByStatus($user, $isStaff),
            'overTime'      => $this->getOverTime($user, $isStaff),
            'byPriority'    => $this->getByPriority($user, $isStaff),
            'recentTickets' => $this->getRecentTickets($user, $isStaff),
            'slaWarnings'   => $this->getSlaWarnings($user, $isStaff, $statusIds),
            'agentWorkload' => $isStaff ? $this->getAgentWorkload($statusIds) : [],
            'recentActivity'=> $this->getRecentActivity($user, $isStaff),
        ];
    }

    // ── Base query with role-based scope ─────────────────────────────────────

    private function base(User $user, bool $isStaff): Builder
    {
        return Ticket::query()
            ->when(!$isStaff, fn($q) => $q->where('user_id', $user->id));
    }

    // ── KPI cards ────────────────────────────────────────────────────────────

    private function getKpis(User $user, bool $isStaff, $statusIds): array
    {
        $resolvedId = $statusIds['resolved'] ?? 0;
        $closedId   = $statusIds['closed']   ?? 0;
        $openId     = $statusIds['open']      ?? 0;
        $inProgId   = $statusIds['in_progress'] ?? 0;

        $open          = (clone $this->base($user, $isStaff))->where('status_id', $openId)->count();
        $inProgress    = (clone $this->base($user, $isStaff))->where('status_id', $inProgId)->count();
        $resolvedToday = (clone $this->base($user, $isStaff))
            ->where('status_id', $resolvedId)
            ->whereDate('updated_at', today())
            ->count();
        $overdue = (clone $this->base($user, $isStaff))
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status_id', [$resolvedId, $closedId])
            ->count();

        // SLA compliance this month: % resolved on time (updated_at <= due_date)
        $resolvedWithSla = (clone $this->base($user, $isStaff))
            ->where('status_id', $resolvedId)
            ->whereNotNull('due_date')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $resolvedOnTime = (clone $this->base($user, $isStaff))
            ->where('status_id', $resolvedId)
            ->whereNotNull('due_date')
            ->whereColumn('updated_at', '<=', 'due_date')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $slaCompliance = $resolvedWithSla > 0
            ? round(($resolvedOnTime / $resolvedWithSla) * 100)
            : null;

        return compact('open', 'inProgress', 'resolvedToday', 'overdue', 'slaCompliance');
    }

    // ── Charts ───────────────────────────────────────────────────────────────

    private function getByStatus(User $user, bool $isStaff): array
    {
        return $this->base($user, $isStaff)
            ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->select(
                'ticket_statuses.slug',
                'ticket_statuses.name',
                DB::raw('count(tickets.id) as count')
            )
            ->groupBy('ticket_statuses.id', 'ticket_statuses.slug', 'ticket_statuses.name')
            ->get()
            ->map(fn($r) => ['slug' => $r->slug, 'name' => $r->name, 'count' => (int) $r->count])
            ->all();
    }

    private function getOverTime(User $user, bool $isStaff): array
    {
        $rows = $this->base($user, $isStaff)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Pad missing days with 0 so chart always shows 14 points
        $result = [];
        for ($i = 13; $i >= 0; $i--) {
            $date     = now()->subDays($i)->format('Y-m-d');
            $result[] = ['date' => $date, 'count' => (int) ($rows[$date] ?? 0)];
        }

        return $result;
    }

    private function getByPriority(User $user, bool $isStaff): array
    {
        return $this->base($user, $isStaff)
            ->join('priorities', 'tickets.priority_id', '=', 'priorities.id')
            ->select(
                'priorities.level',
                'priorities.name',
                DB::raw('count(tickets.id) as count')
            )
            ->groupBy('priorities.id', 'priorities.level', 'priorities.name')
            ->orderBy('priorities.level', 'desc')
            ->get()
            ->map(fn($r) => ['level' => (int) $r->level, 'name' => $r->name, 'count' => (int) $r->count])
            ->all();
    }

    // ── Lists ────────────────────────────────────────────────────────────────

    private function getRecentTickets(User $user, bool $isStaff): array
    {
        return $this->base($user, $isStaff)
            ->latest()
            ->limit(6)
            ->with([
                'user:id,name',
                'assignedUser:id,name',
                'status:id,name,slug',
                'priority:id,name,level',
            ])
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'title'       => $t->title,
                'status'      => $t->status   ? ['name' => $t->status->name,   'slug'  => $t->status->slug]   : null,
                'priority'    => $t->priority  ? ['name' => $t->priority->name, 'level' => $t->priority->level] : null,
                'user'        => $t->user       ? ['name' => $t->user->name]       : null,
                'assigned_to' => $t->assignedUser ? ['name' => $t->assignedUser->name] : null,
                'created_at'  => $t->created_at->toISOString(),
                'is_overdue'  => $t->due_date?->isPast() ?? false,
            ])
            ->all();
    }

    private function getSlaWarnings(User $user, bool $isStaff, $statusIds): array
    {
        $excludedIds = array_filter([
            $statusIds['resolved'] ?? null,
            $statusIds['closed']   ?? null,
        ]);

        return $this->base($user, $isStaff)
            ->whereNotNull('due_date')
            ->whereNotIn('status_id', $excludedIds)
            ->where('due_date', '<', now()->addHours(4))
            ->orderBy('due_date')
            ->limit(5)
            ->with(['assignedUser:id,name', 'status:id,name,slug'])
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'title'       => $t->title,
                'due_date'    => $t->due_date->toISOString(),
                'is_overdue'  => $t->due_date->isPast(),
                'status'      => $t->status       ? ['name' => $t->status->name, 'slug' => $t->status->slug] : null,
                'assigned_to' => $t->assignedUser  ? ['name' => $t->assignedUser->name] : null,
            ])
            ->all();
    }

    private function getAgentWorkload($statusIds): array
    {
        $openIds       = array_filter([$statusIds['open']        ?? null]);
        $inProgressIds = array_filter([$statusIds['in_progress'] ?? null]);

        return User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'agent']))
            ->where('active', true)
            ->withCount([
                'assignedTickets as open_count'        => fn($q) => $q->whereIn('status_id', $openIds)->whereNull('deleted_at'),
                'assignedTickets as in_progress_count' => fn($q) => $q->whereIn('status_id', $inProgressIds)->whereNull('deleted_at'),
            ])
            ->orderByDesc('open_count')
            ->limit(6)
            ->get(['id', 'name'])
            ->map(fn($u) => [
                'id'                => $u->id,
                'name'              => $u->name,
                'open_count'        => (int) $u->open_count,
                'in_progress_count' => (int) $u->in_progress_count,
            ])
            ->all();
    }

    private function getRecentActivity(User $user, bool $isStaff): array
    {
        return TicketHistory::with(['user:id,name', 'ticket:id,title'])
            ->whereHas('ticket', fn($q) => $q->when(!$isStaff, fn($q) => $q->where('user_id', $user->id)))
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn($h) => [
                'ticket_id'    => $h->ticket_id,
                'ticket_title' => $h->ticket?->title,
                'action'       => $h->action,
                'description'  => $h->description,
                'user'         => $h->user ? ['name' => $h->user->name] : null,
                'created_at'   => $h->created_at->toISOString(),
            ])
            ->all();
    }
}
