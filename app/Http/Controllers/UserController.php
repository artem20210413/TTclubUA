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

    public function update(UpdateUserRequest $request, $id)
    {
        try {

            /** @var User $user */
            $user = User::findOrFail($id);

            $user->name = $request->input('name', $user->name); // Если поле пустое, сохраняем старое значение
            $user->email = $request->input('email', $user->email); // Аналогично для других полей
            $user->phone = $request->input('phone', $user->phone);
            $user->telegram_nickname = $request->input('telegram_nickname', $user->telegram_nickname);
            $user->instagram_nickname = $request->input('instagram_nickname', $user->instagram_nickname);
            $user->birth_date = Carbon::parse($request->input('birth_date', $user->birth_date));
            $user->club_entry_date = Carbon::parse($request->input('club_entry_date', $user->club_entry_date));
            $user->occupation_description = $request->input('occupation_description', $user->occupation_description);

            // Сохраняем изменения
            $user->save();

            // Возвращаем успешный ответ
            return response()->json(['message' => 'User updated successfully', 'data' => new UserResource($user)]);

        } catch (ApiException $e) {
            return error($e);
        }
    }

}
