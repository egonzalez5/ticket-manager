<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketRatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'rating'     => $this->rating,
            'comment'    => $this->comment,
            'user'       => $this->whenLoaded('user', fn() => UserResource::make($this->user)),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
