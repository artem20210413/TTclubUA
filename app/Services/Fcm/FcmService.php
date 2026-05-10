<?php

namespace App\Services\Fcm;

use App\Enum\NotificationsPushType;
use App\Jobs\SendSingleFcmPushJob;
use App\Models\FcmToken;
use App\Models\Notification;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmService
{
    /**
     * Відправити Push-повідомлення на конкретний токен
     * * @param FcmToken $fcmToken Модель токена з БД
     * @param string $title Заголовок
     * @param string $body Текст
     * @param NotificationsPushType $type Тип з Enum для логіки у Flutter
     * @param array $extraData Додаткові ID (наприклад, car_id або chat_id)
     */
    public static function sendPush(
        FcmToken              $fcmToken,
        string                $title,
        string                $body,
        NotificationsPushType $type,
        array                 $extraData = []
    )
    {
        $messaging = app('firebase.messaging');

        // Формуємо data payload для Flutter (все має бути string)
        $dataPayload = array_merge([
            'type' => $type->value,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ], array_map('strval', $extraData));

        $message = CloudMessage::fromArray([
            'token' => $fcmToken->token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $dataPayload,
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'high_importance_channel',
                    'sound' => 'default',
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'badge' => 1,
                        'sound' => 'default',
                    ],
                ],
            ],
        ]);

        try {
            return $messaging->send($message);
        } catch (NotFound $e) {
            // Видаляємо токен, якщо додаток видалено
            Log::warning("FCM: Токен не знайдено, видаляємо: {$fcmToken->token}");
            $fcmToken->delete();
        } catch (InvalidMessage $e) {
            Log::error("FCM: Невалідний токен: {$fcmToken->token}");
            $fcmToken->delete();
        } catch (AuthenticationError $e) {
            Log::critical("FCM: Помилка аутентифікації сервісного акаунту.");
        } catch (\Exception $e) {
            Log::error("FCM: Загальна помилка: " . $e->getMessage());
        }

        return null;
    }


    public static function pushNotification(Notification $notification)
    {
        $user = $notification->user;
        $tokens = $user->fcmTokens;


        if ($tokens->isEmpty()) {
            Log::info("No active tokens for user ID: {$user->id}");
            return;
        }

        foreach ($tokens as $token) {

            SendSingleFcmPushJob::dispatch(
                $token,
                $notification->title,
                $notification->body,
                $notification->type,
                $notification->data ?? []
            );
//            (new SendSingleFcmPushJob(
//                $token,
//                $notification->title,
//                $notification->body,
//                $notification->type,
//                $notification->data ?? []
//            ))->handle();
        }
    }
}
