<?php

namespace App\Http\Resources\User;

use App\Enum\EnumTypeMedia;
use App\Http\Resources\CityResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSmallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $this */
//        return parent::toArray($request);
        $birthDate = $this->birth_date ? Carbon::parse($this->birth_date) : null;
//        $clubEntryDate = $this->club_entry_date ? Carbon::parse($this->club_entry_date) : null;


        $default = asset("storage/default/" . EnumTypeMedia::PROFILE_PICTURE->value . ".webp");
        $profileImage = $this->getFirstMediaUrl(EnumTypeMedia::PROFILE_PICTURE->value) ?: $default;

//        $imageUrls = $this->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value)->map(function ($media) {
//            return $media->getUrl();
//        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'instagram_nickname' => $this->instagram_nickname,
            'profile_image' => $profileImage,
            'is_birthday_today' => $this->birth_date
                ? \Carbon\Carbon::parse($this->birth_date)->isBirthday()
                : false,
        ];
    }
}
