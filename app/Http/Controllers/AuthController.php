<?php

namespace App\Http\Controllers;

use App\Enum\EnumTelegramEvents;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\ChangePasswordByUserRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        $deviceType = $this->getDeviceType($request);
        $token = $user->createToken("auth_$deviceType")->plainTextToken;

        return success('Login successful', [
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }


    public function sendCode(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'min:5', 'max:32'], // подправишь под свой формат
        ]);

        $phone = $data['phone'];

        /** @var User|null $user */
        $user = User::findByPhone($phone);
        try {

            if (!$user) {
                throw new ApiException('Користувача з таким номером не знайдено.', 0, 404);
            }

            if (empty($user->telegram_id)) {
                throw new ApiException('Будь ласка, підтвердіть номер телефону через Telegram-бот, перш ніж входити.', 0, 400);
            }

            // генерируем и сохраняем код в кеш
            $code = $user->generateAndStoreLoginCode();
            Auth::login($user);
            $text = TelegramBotHelpers::generationTextAuthCode($code, 10);
            $bot = new TelegramBot(EnumTelegramEvents::MY);
            $bot->sendMessage($text);
            Auth::logout();

            return response()->json([
                'success' => true,
                'message' => 'Код для входу відправлено в Telegram.',
            ]);

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string'
        ]);

        try {

            $phone = $request->phone;
            $user = User::findByPhone($phone);
            $code = trim($request->code);

            if (!$user) {
                throw new ApiException('Користувача не знайдено.', 0, 404);
            }

            $cachedCode = $user->getLoginCodeFromCache();

            if (!$cachedCode || $code !== $cachedCode) {
                throw new ApiException('Код не знайдено або термін дії минув..', 0, 404);
            }

            $user->clearCode();
            $deviceType = $this->getDeviceType($request);
            $token = $user->createToken("tg_$deviceType")->plainTextToken;

            return success("Авторизація успішна", [
                'token' => $token,
                'user' => new UserResource($user),
            ]);

        } catch (ApiException $e) {
            return error($e);
        }
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

    private function getDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/(android)/i', $userAgent)) {
            return 'android';
        }

        if (preg_match('/(iphone|ipad|ipod)/i', $userAgent)) {
            return 'ios';
        }

        if (preg_match('/(windows)/i', $userAgent)) {
            return 'windows';
        }

        return 'other';
    }

}
