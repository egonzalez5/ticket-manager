<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TicketFilter
{
    public static function apply(Request $request, Builder $query): Builder
    {
        return $query
            ->when(
                $request->filled('status'),
                fn($q) => $q->whereHas('status', fn($q) => $q->where('slug', $request->status))
            )
            ->when(
                $request->filled('priority'),
                fn($q) => $q->where('priority_id', $request->integer('priority'))
            )
            ->when(
                $request->filled('assigned_to'),
                fn($q) => $q->where('assigned_to', $request->integer('assigned_to'))
            )
            ->when(
                $request->boolean('mine'),
                fn($q) => $q->where('user_id', $request->user()->id)
            )
            ->when(
                $request->boolean('overdue'),
                fn($q) => $q->whereNotNull('due_date')->where('due_date', '<', now())
            )
            ->when(
                $request->filled('search'),
                fn($q) => $q->where(fn($q) => $q
                    ->where('title', 'ilike', '%' . $request->search . '%')
                    ->orWhere('description', 'ilike', '%' . $request->search . '%')
                )
            );
    }
}
