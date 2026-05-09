<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'due_date'    => $this->due_date?->toISOString(),
            'is_overdue'  => $this->due_date?->isPast() ?? false,
            'created_at'  => $this->created_at->toISOString(),
            'updated_at'  => $this->updated_at->toISOString(),

            'status' => $this->whenLoaded('status', fn() => [
                'id'   => $this->status->id,
                'name' => $this->status->name,
                'slug' => $this->status->slug,
            ]),
            'priority' => $this->whenLoaded('priority', fn() => [
                'id'    => $this->priority->id,
                'name'  => $this->priority->name,
                'level' => $this->priority->level,
            ]),
            'category' => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),
            'sla' => $this->whenLoaded('sla', fn() => $this->sla ? [
                'id'              => $this->sla->id,
                'name'            => $this->sla->name,
                'response_time'   => $this->sla->response_time,
                'resolution_time' => $this->sla->resolution_time,
            ] : null),

            'user'        => $this->whenLoaded('user', fn() => UserResource::make($this->user)),
            'assigned_to' => $this->whenLoaded('assignedUser', fn() => $this->assignedUser
                ? UserResource::make($this->assignedUser)
                : null
            ),

            'tags' => $this->whenLoaded('tags', fn() => $this->tags->map(fn($t) => [
                'id'   => $t->id,
                'name' => $t->name,
            ])),

            'messages'    => TicketMessageResource::collection($this->whenLoaded('messages')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),

            'history' => $this->whenLoaded('history', fn() => $this->history->map(fn($h) => [
                'id'          => $h->id,
                'action'      => $h->action,
                'description' => $h->description,
                'created_at'  => $h->created_at->toISOString(),
                'user'        => ['id' => $h->user_id, 'name' => $h->user?->name],
            ])),

            'rating' => $this->whenLoaded('ratings', fn() => $this->ratings->isNotEmpty()
                ? TicketRatingResource::make($this->ratings->first())
                : null
            ),
        ];
    }
}
