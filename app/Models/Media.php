<?php

namespace App\Models;

use App\Http\Controllers\Api\ApiException;


/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string|null $telegram_nickname
 * @property string|null $instagram_nickname
 * @property string $phone
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property \Illuminate\Support\Carbon|null $club_entry_date
 * @property string|null $occupation_description
 * @property \Illuminate\Support\Carbon|null $phone_verified_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $approve_verified_at
 * @property int $active
 * @property string $password
 * @property string|null $profile_photo_path
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property City $cities
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Session[] $sessions
 * @property-read \Illuminate\Database\Eloquent\Collection|Car[] $cars
 */
class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    /**
     * @param int $id
     * @return User
     * @throws ApiException
     */
    static function findOrFail(int $id): Media
    {
        $user = Media::find($id);

        if (!$user)
            throw  new ApiException('Медиа-файл не найден', 0, 400);


        return $user;
    }

}
