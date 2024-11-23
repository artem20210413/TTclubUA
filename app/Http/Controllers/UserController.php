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


class UserController extends Controller
{

    public function user(Request $request)
    {
        return success(data: ['user' => new UserResource($request->user())]);
    }

    public function update(UpdateUserRequest $request)
    {
        try {
            /** @var User $user */
            $user = $request->user();

            $user->updateCustom($request);

            return success(massage: 'Користувача успішно оновлено', data: ['user' => new UserResource($user)]);

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function updateById(UpdateUserRequest $request, int $id)
    {
        try {

            /** @var User $user */
            $user = User::findOrFail($id);

            $user->updateCustom($request);

            return success(massage: 'Користувача успішно оновлено', data: ['user' => new UserResource($user)]);

        } catch (ApiException $e) {
            return error($e);
        }
    }

}
