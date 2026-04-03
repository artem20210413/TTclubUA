<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
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
        $imageUrls = $this->getMedia(EnumTypeMedia::PHOTO_DRAW->value)->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
            ];
        });
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'allow_multiple_wins' => $this->allow_multiple_wins,
            'is_public' => $this->is_public,
//            'registration_until' => $this->registration_until,
            'registration_until' => $this->registration_until?->format('Y-m-d H:i:s'),
            'is_participating' => $isParticipating,
            'images' => $imageUrls,
            'participants' => ParticipantResource::collection($this->participants),
            'prizes' => PrizeResource::collection($this->prizes),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
