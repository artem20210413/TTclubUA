<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrls = $this->getMedia(EnumTypeMedia::PHOTO_PUBLICATION->value)->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
            ];
        });

        return [
            'id' => $this->id,
            'publication_type_id' => $this->publication_type_id,
            'title' => $this->title,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'images' => $imageUrls,
            'active' => $this->active,
        ];
    }
}
