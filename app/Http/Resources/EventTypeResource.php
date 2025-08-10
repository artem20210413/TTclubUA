<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $default = asset("storage/default/" . EnumTypeMedia::PROFILE_PICTURE->value . ".webp");
        $image = $this->getFirstMediaUrl(EnumTypeMedia::PHOTO_EVENT_TYPE->value) ?: $default;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'sort' => $this->sort,
            'image' => $image,
        ];
    }
}
