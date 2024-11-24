<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\CityResource;
use App\Http\Resources\UserResource;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\Request;


class UserController extends Controller
{

    public function user(Request $request)
    {
        return success(data: ['user' => new UserResource($request->user())]);
    }

    public function all(Request $request)
    {
        return success(data: ['users' => UserResource::collection(User::all())]);
    }

    public function cities()
    {
        return success(data: ['cities' => CityResource::collection(City::all())]);
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
