<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use App\Models\Event;
use App\Models\Goods;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Goods $this */
        $imageUrls = $this->getMedia(EnumTypeMedia::PHOTO_GOODS->value)->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
            ];
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'priority' => $this->priority,
            'images' => $imageUrls,
            'active' => $this->active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
