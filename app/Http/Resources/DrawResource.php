<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DrawResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isParticipating = false;
        if (Auth::check()) {
            $isParticipating = $this->participants()->where('user_id', Auth::id())->exists();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'allow_multiple_wins' => $this->allow_multiple_wins,
            'is_public' => $this->is_public,
            'registration_until' => $this->registration_until,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_participating' => $isParticipating,
            'participants' => ParticipantResource::collection($this->participants),
            'prizes' => PrizeResource::collection($this->prizes),
        ];
    }
}
