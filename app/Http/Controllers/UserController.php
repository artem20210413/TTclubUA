<?php

namespace App\Http\Controllers;

use App\Eloquent\UserEloquent;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\Car\CarResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserWithCarsResource;
use App\Models\User;
use Illuminate\Http\Request;


class UserController extends Controller
{

    public function user(Request $request)
    {
        return success(data: ['user' => new UserResource($request->user())]);
    }

    public function search(string $search, Request $request)
    {
        $search = str_replace(' ', '%', trim($search));
        $query = User::query();

        $query = UserEloquent::search($query, $search);

        return success(data: UserWithCarsResource::collection($query->paginate(10)));
    }

    public function all(Request $request)
    {
        return success(data: ['users' => UserResource::collection(User::all())]);
    }

    public function myCars(Request $request)
    {
        /** @var User $user */
        $user = $request->user();


        return success(data: CarResource::collection($user->cars));
    }


    public function update(UpdateUserRequest $request)
    {
        try {
            /** @var User $user */
            $user = $request->user();

            $user->updateCustom($request);

            return success(massage: 'Користувача успішно оновлено', data: ['user' => new UserResource($user->refresh())]);

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function updateById(int $id, UpdateUserRequest $request)
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

    public function userChangeActive(User $user, UpdateUserRequest $request)
    {
        try {
            if ($request->user()->id === $user->id) throw new ApiException('Власний статус міняти заборонено', 1, 403);

            $user->active = !$user->active;
            $user->save();

            return success(massage: 'Користувача успішно оновлено', data: ['user' => new UserResource($user)]);

        } catch (ApiException $e) {
            return error($e);
        }
    }


}
