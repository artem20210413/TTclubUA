<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];

        if ($this->users_count !== null) {
            $data['users_count'] = $this->users_count;
            $data['avatar'] = $this->users_count === 1
                ? ($this->users->first()?->getFirstMediaUrl(EnumTypeMedia::PROFILE_PICTURE->value) ?: null)
                : null;
        }

        return $data;
    }
}
