<?php

namespace App\Http\Controllers;

use App\Eloquent\UserEloquent;
use App\Enum\EnumTelegramChats;
use App\Http\Resources\User\UserResource;
use App\Services\Telegram\TelegramBot;
use Illuminate\Http\Request;
use Telegram\Bot\Api;


class TestController extends Controller
{

//    public function fafa(Request $request)
//    {
//      $res =  Http::get("https://api.telegram.org/bot{$request->token}/sendMessage", [
//            'chat_id' => $request->chatId,
//            'text' => '`@olha_mo` fa-fa',
//            'parse_mode' => 'Markdown',
//        ]);
//        dd($res->status(), $res->body(), $request->all(),);
//    }
    protected Api $telegram;

    public function __construct()
    {
        $this->telegram = new Api(config('services.telegram.token'));
    }

    public function fafa(Request $request)
    {
//        $userId = '407600221';
//        $username = 'olha_mo';
//        $message = "<a href='tg://user?id={$userId}'>@$username</a> fa-fa"; // Упоминание
////        $message = "[@$username](tg://user?id={$userId}) fa-fa";
////        $message = "<a href='https://t.me/{$username}'>@$username</a> fa-fa";
//
//        return $this->telegram->sendMessage([
//            'chat_id' => $request->chatId,
//            'text' => $message,
////            'parse_mode' => 'MarkdownV2',
//            'parse_mode' => 'HTML',
//        ]);
    }

    public function get(Request $request)
    {
//        dd($s->getBirthdayPeople()->count());
//        return success(null, UserResource::collection($users));
//        $updates = $this->telegram->getUpdates();
//        foreach ($updates as $update) {
//            if (!$update->getMessage()->isEmpty()) {
//                // Извлекаем сообщение
//                $message = $update->getMessage();
//
//                // Получаем информацию о пользователе
//                $user = $message->getFrom();
//                $userId = $user->getId();
//                $username = $user->getUsername();
//                // Теперь у тебя есть ID пользователя
//                dump("User ID: $userId, Username: $username");
//            } else {
//                dump('---');
//            }
//
//        }
    }

}
