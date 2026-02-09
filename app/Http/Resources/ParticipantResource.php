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
            'weight' => $this->weight,
            'is_winner' => $this->is_winner,
            'user_id' => $this->user_id,
            'user_name' => $this?->user->name ?? $this->name_manual,
            'contact_manual' => $this->contact_manual,
        ];
    }
}
