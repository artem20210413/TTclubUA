<?php

namespace App\Services\Fcm;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class Fcm
{
    public static function sendPush($fcm_token, $title = 'title1', $body = 'body1')
    {
        $messaging = app('firebase.messaging');

        // Створюємо повідомлення через масив — це найнадійніший спосіб у нових версіях
        $message = CloudMessage::fromArray([
            'token' => $fcm_token,
            'notification' => [
//                'channel_id' => 'high_importance_channel', // МАЄ ЗБІГАТИСЯ З FLUTTER
                'title' => $title,
                'body' => $body,
//                'image' => "https://static.tildacdn.net/tild6532-3866-4638-b566-633765383033/578719-1603001714.jpg",
            ],
//            'data' => [
//                'type' => 'broadcast',
//                'time' => now()->toDateTimeString()
//                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
//                'type' => 'giveaway',
//            ],
//            'android' => [
//                'priority' => 'high',
//                'notification' => [
//                    'channel_id' => 'high_importance_channel', // Важливо для Android 8.0+
//                ],
//            ],
        ]);// 1. Фіксуємо час старту

        try {
            // 2. Виконуємо відправку
            $res = $messaging->send($message);
            dd($res);


        } catch (NotFound $e) {
            // Аналог статусу 404/410: Токен не знайдено (додаток видалено)
            // ТУТ ТВОЯ ЛОГІКА ВИДАЛЕННЯ: FcmToken::where('fcm_token', $fcm_token)->delete();
            dump("FCM Token видалено (NotFound): {$fcm_token}");
            Log::warning("FCM Token видалено (NotFound): {$fcm_token}");

        } catch (InvalidMessage $e) {
            // Аналог статусу 400: Токен має невірний формат
            dump("FCM Token невалідний (Invalid): {$fcm_token}");
            Log::error("FCM Token невалідний: {$fcm_token}. Error: " . $e->getMessage());

        } catch (AuthenticationError $e) {
            // Проблема з ключами доступу на сервері
            Log::critical("FCM Auth Error: Перевір сервісний аккаунт Firebase.");
            throw $e;

        } catch (\Exception $e) {
            // Будь-яка інша помилка (наприклад, проблеми з мережею)
            Log::error("FCM General Error для токена {$fcm_token}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function sendPush2($fcm_token, $title = 'title2', $body = 'body2')
    {

        $path = storage_path('app/firebase/service-account.json');
        $serviceAccount = json_decode(file_get_contents($path), true);

        $url = "https://fcm.googleapis.com/v1/projects/{$serviceAccount['project_id']}/messages:send";

        // Используем Google Auth напрямую (без Google_Client)
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $path
        );

        // Получаем токен
        $token = $credentials->fetchAuthToken();
        $accessToken = $token['access_token'];
        $start = microtime(true);
        $results['success'] = 0;
        $results['failure'] = 0;
        $results['deleted'] = 0;


        try {
            $response = Http::withToken($accessToken)->post($url, [
                'message' => [
                    'token' => $fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => [
                        'type' => 'broadcast',
                        'time' => now()->toDateTimeString()
                    ],
                    'android' => [
                        'priority' => 'high',
                    ],
//                    'data' => array_merge($data, [
//                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
//                        'sent_at' => now()->toDateTimeString(),
//                    ]),
//                    'android' => [
//                        'priority' => 'high',
//                        'notification' => [
//                            'sound' => 'default',
//                        ],
//                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                            ],
                        ],
                    ],
                ]
            ]);

            if ($response->successful()) {
                $results['success']++;
            } else {
                $results['failure']++;
                $status = $response->status();

                // Якщо токен протух (додаток видалено або токен скинуто) — видаляємо з бази
                if (in_array($status, [404, 410, 400])) {
//                    $device->delete();
                    $results['deleted']++;
                    dump("FCM Token видалено (Invalid): {$fcm_token}");
//                    Log::warning("FCM Token видалено (Invalid): {$device->token}");
                } else {
                    dump("FCM Error для токена {$fcm_token}: " . json_encode($response->json()));
                    Log::error("FCM Error для токена {$fcm_token}: " . json_encode($response->json()));
                }
            }

            // 3. Рахуємо різницю
            $duration = round(microtime(true) - $start, 4);
            dump($results);
            // Виводимо в консоль/дамп
            dd("Пуш відправлено успішно! Час виконання: {$duration} сек.");


        } catch (\Exception $e) {
            $duration = round(microtime(true) - $start, 4);
            dump("Помилка через {$duration} сек.");
            throw $e;
        }
    }
}
