<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        return success(data: ['user' => new UserResource($request->user())]);
    }

    public function register(RegisterRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->setPhone($request->phone);
        $user->setPassword($request->password);
        $user->save();

        return success('User registered successfully', ['user' => new UserResource($user->fresh())]);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('password');

        $login = $request->input('login');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials[$fieldType] = $login;

        if (!Auth::attempt($credentials)) {
            return error(new ApiException('Invalid login details', 401, 0));
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return success('Login successful', [
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

}
