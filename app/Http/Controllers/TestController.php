<?php

namespace App\Http\Controllers;

use App\Console\Commands\Tg\SendingStatisticsMention;
use App\Enum\NotificationsPushType;
use App\Models\Notification;
use App\Models\User;
use App\Services\Fcm\Fcm;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test(Request $request)
    {

        //        $user = User::first();
        //
        //        if (!$user) return "Користувачів не знайдено!";
        //
        //        // 2. Створюємо сповіщення
        //        // Це автоматично закине джобу SendPushNotificationJob у чергу
        //        $notification = Notification::create([
        //            'user_id' => $user->id,
        //            'title'   => 'Тестовий заїзд! 🏎',
        //            'body'    => 'Це перевірка черги та Firebase. Фа-фа!',
        //            'type'    => NotificationsPushType::FA_FA,
        //            'data'    => [
        //                'car_id' => 1,
        //                'test_mode' => true
        //            ],
        //        ]);
        //
        //        return "Сповіщення збережено в БД (ID: {$notification->id}). Чекаємо на обробку черги...";
        //        Fcm::sendPush('fw_omk1kQICdKUCgOPlm29:APA91bEgeMrA3FQ084NPkW0hxpg92KSQCWrVESR_kkgoPNJffcNh7pZFRVyKWCXd66Ym6AZqzwQ73OviSTqV7PYBPkhaj8OhOPcPHv_RUXMvwE88E16XYVE');
        //        $h = new SendingStatisticsMention();
        //        $h->handle();
        //        dd(33);

    }
}
