<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\ChangePasswordByUserRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
