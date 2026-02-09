<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'draw_id' => $this->draw_id,
            'user_id' => $this->user_id,
            'name_manual' => $this->name_manual,
            'contact_manual' => $this->contact_manual,
            'weight' => $this->weight,
            'is_winner' => $this->is_winner,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
