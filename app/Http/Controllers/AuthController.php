<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\ChangePasswordByUserRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
//        $this->middleware('permission:create post', ['only' => ['create', 'store']]);
//        $this->middleware('permission:edit post', ['only' => ['edit', 'update']]);
//        $this->middleware('permission:delete post', ['only' => ['destroy']]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return success('Successfully logged out');
    }

    public function register(RegisterRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->setPhone($request->phone);
        $user->setPassword($request->password);
        $user->telegram_nickname = $request->telegram_nickname;
        $user->instagram_nickname = $request->instagram_nickname;
        $user->birth_date = Carbon::parse($request->birth_date);
//        $user->club_entry_date = Carbon::parse($request->club_entry_date);
        $user->occupation_description = $request->occupation_description;
        $user->save();

        if ($request->filled('cities')) {
            $user->cities()->sync($request->cities);
        }

        return success('User registered successfully', ['user' => new UserResource($user->fresh())]);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('password');

        $login = $request->input('login');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials[$fieldType] = $login;

        if (!Auth::attempt($credentials)) {
            return error(new ApiException('Invalid login details', 0, 401));
        }

        $user = Auth::user();
        if (!$user->active) {
            Auth::logout();
            return error(new ApiException('Invalid login details', 0, 401));
//            return error(new ApiException('Your account is deactivated', 0, 403));
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return success('Login successful', [
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return error(new ApiException('Поточний пароль неправильний', 0, 400));
        }

        $user->setPassword($request->new_password);
        $user->save();

        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()
            ->where('id', '!=', $currentTokenId)
            ->delete();

        return success('Пароль успішно оновлено');
    }

    public function changePasswordByUser(int $id, ChangePasswordByUserRequest $request)
    {
        try {
            $user = User::findOrFail($id);

            $user->setPassword($request->new_password);
            $user->save();

            return success('Пароль успішно оновлено', new UserResource($user->refresh()));
        } catch (ApiException $e) {
            return error($e);
        }
    }

}
