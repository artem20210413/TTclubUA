<?php

namespace App\Http\Controllers;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\ProfilePictureRequest;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\Request;


class MediaController extends Controller
{

    public function updateProfilePicture(ProfilePictureRequest $request)
    {
        $user = auth()->user();

        $image = $request->file('profile_image');

        if (!$image) return error(new ApiException('Фото відсутнє.', 0, 400));

        $imageWebp = new ImageWebpService($image);
        $imageWebp->convert(EnumImageQuality::FULL_HD);

        $user->dropProfileImages();

        $user->addMediaFromStream($imageWebp->first()->stream())
            ->usingFileName('profile_image.webp')
            ->toMediaCollection(EnumTypeMedia::PROFILE_PICTURE->value);

        return success('Фото профілю успішно оновлено.', new UserResource($request->user()->refresh()));
    }

    public function deleteProfilePicture(Request $request)
    {
        auth()->user()->dropProfileImages();

        return success('Фото успішно видалено.', new UserResource($request->user()->refresh()));
    }

//    public function deleteProfilePicture(int $id, Request $request)
//    {
//        // Получаем текущего пользователя
//        $user = auth()->user();
//
//        // Ищем медиа-файл по ID в коллекции пользователя
//        $media = $user->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value)->firstWhere('id', $id);
//
//        if (!$media) {
//            return error(new ApiException('Фото не знайдено.', 0, 404));
//        }
//
//        // Удаляем медиа-файл
//        $media->delete();
//
//        return success('Фото успішно видалено.', new UserResource($request->user()));
//    }


    public function deleteMediaById($mediaId)
    {
        try {
            $media = Media::findOrFail($mediaId);
            $media->delete();

            return success('Медиа-файл успешно удалён.');

        } catch (ApiException $e) {
            return error($e);
        }
    }


}
