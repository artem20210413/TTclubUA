<?php

namespace App\Http\Controllers;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\ProfilePictureRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\Request;

//use Intervention\Image\ImageManager;
//use Intervention\Image\Drivers\Gd\Driver;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


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
        // Ищем медиа-файл по ID
        $media = Media::find($mediaId);

        if (!$media) {
            return error(new ApiException('Медиа-файл не найден.', 0, 404));
        }

        $media->delete();

        return success('Медиа-файл успешно удалён.');
    }

    public function addProfileCollection(Request $request)
    {
        $user = auth()->user();

        $images = $request->file('images'); // Массив изображений, переданных через запрос

        if (!$images || !is_array($images)) {
            return error(new ApiException('Фото відсутні.', 0, 400));
        }
        $imageWebp = new ImageWebpService(...$images);
        $images = $imageWebp->convert(EnumImageQuality::HD);
        // Для каждой картинки обрабатываем и сохраняем её
        foreach ($images as $image) {
            $user->addMediaFromStream($image->stream())
                ->usingFileName('collection_image_' . time() . '.webp')
                ->toMediaCollection(EnumTypeMedia::PHOTO_COLLECTION->value);
        }

        return success('Колекція профілю успішно оновлено.', new UserResource($request->user()));
    }


}
