<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    public function toArray($request): array
    {

        $imageUrls = $this->getMedia(EnumTypeMedia::PHOTO_PARTNER->value)->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
            ];
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'website_url' => $this->website_url,
            'instagram_url' => $this->instagram_url,
            'google_maps_url' => $this->google_maps_url,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'has_promotions' => $this->when(isset($this->promotions_count), $this->promotions_count > 0),
            'promotions_count' => $this->when(isset($this->promotions_count), $this->promotions_count),
            'photos' => $imageUrls,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
