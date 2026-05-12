<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAgent()) return true;
        return $ticket->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->isAgent()) return true;
        return $ticket->user_id === $user->id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return false; // solo admin — resuelto por before()
    }

    public function changeStatus(User $user, Ticket $ticket): bool
    {
        return $user->isAgent();
    }

    public function changePriority(User $user, Ticket $ticket): bool
    {
        return $user->isAgent();
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->isAgent();
    }

    // Cubre tanto escribir notas internas como verlas
    public function internalNote(User $user, Ticket $ticket): bool
    {
        return $user->isAgent();
    }
}
