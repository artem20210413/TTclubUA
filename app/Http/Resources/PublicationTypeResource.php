<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $default = asset("storage/default/" . EnumTypeMedia::PROFILE_PICTURE->value . ".webp");
        $image = $this->getFirstMediaUrl(EnumTypeMedia::PHOTO_PUBLICATION->value) ?: $default;

        return [
            'id' => $this->id,
            'image' => $image,
            'name' => $this->name,
            'description' => $this->description,
            'sort' => $this->sort,
        ];
    }
}
