<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'message'     => $this->message,
            'is_internal' => $this->is_internal,
            'user'        => $this->whenLoaded('user', fn() => $this->user ? [
                'id'   => $this->user->id,
                'name' => $this->user->name,
                'role' => $this->user->relationLoaded('role') && $this->user->role ? [
                    'id'   => $this->user->role->id,
                    'name' => $this->user->role->name,
                    'slug' => $this->user->role->slug,
                ] : null,
            ] : null),
            'attachments' => $this->whenLoaded('attachments', fn() =>
                $this->attachments->map(fn($a) => AttachmentResource::make($a)->resolve())->values()->all()
            ),
            'created_at'  => $this->created_at->toISOString(),
        ];
    }
}
