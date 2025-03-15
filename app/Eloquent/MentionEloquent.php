<?php

namespace App\Eloquent;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTelegramChats;
use App\Enum\EnumTypeMedia;
use App\Http\Requests\MentionRequest;
use App\Models\Car;
use App\Models\Mention;
use App\Models\User;
use App\Services\Image\ImageWebpService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Image;

class MentionEloquent
{

    public static function create(Car $car, MentionRequest $request): Mention
    {

        $owner = auth()->user();

        $file = $request->file('file');
        $description = $request->description;


        $mention = new Mention();
        $mention->owner_id = $owner->id;
        $mention->car_id = $car->id;
        $mention->description = $description;
        $mention->save();

        if ($file) {
            $imageWebp = new ImageWebpService($file);
            $imageWebp->convert(EnumImageQuality::HD);
            $mention->addMediaFromStream($imageWebp->first()->stream())
                ->usingFileName(EnumTypeMedia::PHOTO_MENTION->value . '.webp')
                ->toMediaCollection(EnumTypeMedia::PHOTO_MENTION->value);
        }

        return $mention;
    }

    public static function delete(Mention $mention): ?bool
    {
        if ($mention->hasMedia(EnumTypeMedia::PROFILE_PICTURE->value)) {
            $mention->getMedia(EnumTypeMedia::PROFILE_PICTURE->value)->each->delete();
        }
        return $mention->delete();
    }


}
