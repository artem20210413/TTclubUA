<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    public function toArray($request): array
    {

        $imageUrls = $this->getMedia(EnumTypeMedia::PHOTO_PARTNER_PROMOTION->value)->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
            ];
        });

        return [
            'id' => $this->id,
            'partner_id' => $this->partner_id,
            'promo_title' => $this->promo_title,
            'promo_description' => $this->promo_description,
            'discount_value' => $this->discount_value,
            'promo_code' => $this->promo_code,
            'is_exclusive' => $this->is_exclusive,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'photos' => $imageUrls,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
