<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrizeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrls = $this->getMedia(EnumTypeMedia::PHOTO_DRAW->value)->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
            ];
        });

        return [
            'id' => $this->id,
            'draw_id' => $this->draw_id,
            'title' => $this->title,
            'quantity' => $this->quantity,
            'sort_order' => $this->sort_order,
            'winner_participant_id' => $this->winner_participant_id,
            'images' => $imageUrls,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
