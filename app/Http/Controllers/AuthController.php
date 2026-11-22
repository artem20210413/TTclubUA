<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\ChangePasswordByUserRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Notifications\AuthCodeNotification;
use App\Notifications\LoginCodeRevokedNotification;
use App\Notifications\LoginSuccessNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

// Явно з namespace: глобальну defer() перекриває розширення Swoole (стоїть у контейнері Sail).
use function Illuminate\Support\defer;

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
        $user = new User;
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
        $env = $request->header('X-Client-Platform', 'unknown');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials[$fieldType] = $login;

        if (! Auth::attempt($credentials)) {
            return error(new ApiException('Invalid login details', 0, 401));
        }

        $user = Auth::user();
        if (! $user->active) {
            Auth::logout();

            return error(new ApiException('Invalid login details', 0, 401));
            //            return error(new ApiException('Your account is deactivated', 0, 403));
        }

        $user = Auth::user();
        $token = $user->createToken("auth_$env")->plainTextToken;

        if ($user->telegram_id) {
            $this->sendLoginAlert($user, LoginSuccessNotification::METHOD_PASSWORD, $env, $request->ip());
        }

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
        $ip = $request->ip();

        Log::info('Запит коду авторизації', [
            'номер' => $phone,
            'ip_адреса' => $ip,
            'пристрій' => $request->userAgent(),
        ]);
        /** @var User|null $user */
        $user = User::findByPhone($phone);
        try {

            if (! $user) {
                Log::warning('Спроба входу: користувача не знайдено', [
                    'номер' => $phone,
                    'ip_адреса' => $ip,
                ]);
                throw new ApiException('Користувача з таким номером не знайдено.', 0, 404);
            }

            // Деактивованому — та сама відповідь, що й невідомому номеру (як і при вході за паролем).
            if (! $user->active) {
                Log::notice('Спроба входу: акаунт деактивовано', [
                    'user_id' => $user->id,
                    'ip_адреса' => $ip,
                ]);
                throw new ApiException('Користувача з таким номером не знайдено.', 0, 404);
            }

            if (empty($user->telegram_id)) {
                Log::notice('Спроба входу: Telegram ID відсутній', [
                    'user_id' => $user->id,
                    'імʼя' => $user->name,
                    'ip_адреса' => $ip,
                ]);
                throw new ApiException('Будь ласка, підтвердіть номер телефону через Telegram-бот, перш ніж входити.', 0, 400);
            }

            // генерируем и сохраняем код в кеш
            $code = $user->generateAndStoreLoginCode();

            // Universal Link (https) — тільки такі посилання Telegram відкриває напряму з inline-кнопки.
            // Кастомна ttclubua:// схема тут не використовується (Telegram її не підтримує в кнопках),
            // застосунок перехоплює цей https-лінк сам через Associated Domains / App Links.
            $universalLink = rtrim(config('mobile.universal_link_host'), '/').'/auth/tg-code?'.http_build_query([
                'phone' => $phone,
                'code' => $code,
            ]);

            $user->notify(new AuthCodeNotification($code, $universalLink, 10));

            Log::info('Код авторизації успішно надіслано в Telegram', [
                'user_id' => $user->id,
                'імʼя' => $user->name,
                'ip_адреса' => $ip,
                'час_відправки' => now()->toDateTimeString(),
            ]);

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
            'code' => 'required|string',
        ]);

        try {

            $env = $request->header('X-Client-Platform', 'unknown');
            $phone = $request->phone;
            $user = User::findByPhone($phone);
            $code = trim($request->code);

            // Деактивований не входить і з кодом, виданим до деактивації.
            if (! $user || ! $user->active) {
                throw new ApiException('Користувача не знайдено.', 0, 404);
            }

            $cachedCode = $user->getLoginCodeFromCache();

            if (! $cachedCode) {
                throw new ApiException('Код не знайдено або термін дії минув..', 0, 404);
            }

            // Ліміт невдалих спроб: після max_attempts помилок наступна спроба анулює код
            // (навіть якщо вона правильна), і користувач отримує про це повідомлення в Telegram.
            $maxAttempts = (int) config('auth.login_code.max_attempts', 5);

            if ($user->getLoginCodeFailedAttempts() >= $maxAttempts) {
                $user->clearCode();
                $user->notify(new LoginCodeRevokedNotification($maxAttempts));

                Log::warning('Код входу анульовано: перевищено кількість спроб', [
                    'user_id' => $user->id,
                    'ip_адреса' => $request->ip(),
                ]);

                throw new ApiException('Забагато невдалих спроб. Код анульовано — запросіть новий.', 0, 429);
            }

            if ($code !== $cachedCode) {
                $user->incrementLoginCodeFailedAttempts();

                throw new ApiException('Код не знайдено або термін дії минув..', 0, 404);
            }

            $user->clearCode();
            $token = $user->createToken("tg_$env")->plainTextToken;

            $this->sendLoginAlert($user, LoginSuccessNotification::METHOD_CODE, $env, $request->ip());

            return success('Авторизація успішна', [
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

        if (! Hash::check($request->current_password, $user->password)) {
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

            // Новий пароль від адміна завершує всі сесії користувача. Якщо адмін міняє пароль
            // собі, поточна сесія лишається (як у changePassword).
            $tokens = $user->tokens();
            $currentToken = $request->user()->currentAccessToken();
            if ($user->id === $request->user()->id && $currentToken instanceof PersonalAccessToken) {
                $tokens->whereKeyNot($currentToken->getKey());
            }
            $tokens->delete();

            return success('Пароль успішно оновлено', new UserResource($user->refresh()));
        } catch (ApiException $e) {
            return error($e);
        }
    }

    /**
     * Повідомлення про вхід із кнопкою «Це не я» надсилається вже після відповіді клієнту (defer):
     * вхід не чекає на Telegram і не падає, якщо Telegram недоступний чи не налаштований.
     */
    private function sendLoginAlert(User $user, string $method, string $platform, ?string $ip): void
    {
        $notification = new LoginSuccessNotification($method, $platform, $ip, now());

        defer(function () use ($user, $notification) {
            try {
                $user->notify($notification);
            } catch (\Throwable $e) {
                Log::error('Не вдалося надіслати повідомлення про вхід: '.$e->getMessage(), ['user_id' => $user->id]);
            }
        });
    }
}
