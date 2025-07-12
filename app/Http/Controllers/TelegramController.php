<?php

namespace App\Http\Controllers;

use App\Eloquent\FinanceEloquent;
use App\Enum\EnumMonoAccount;
use App\Enum\EnumMonoStatus;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\FinanceRequest;
use App\Http\Resources\FinanceWithUserResource;
use App\Models\Finance;
use App\Models\MonoTransaction;
use App\Models\User;
use App\Services\Telegram\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{

    public function webhook(Request $request)
    {
//https://api.telegram.org/bot<BOT_TOKEN>/setWebhook?url=https://tt.tishchenko.kiev.ua/api/telegram/webhook
        Log::info("webhook request received", [$request->all()]);
        $tgBot = new TelegramBot();
        $tg = $tgBot->getTelegram();
        $message = $request->input('message');
        if (!$message) return;
        $chatId = $message['chat']['id'];
        $fromId = $message['from']['id'];
        if ($fromId !== $chatId) return;

        $text = $message['text'] ?? '';

        match (trim($text)) {
            '/start', '/hi' => $tg->sendMessage([
                'chat_id' => $chatId,
                'text' => "Привет! Я твой Telegram-бот 😊",
            ]),

            '/help' => $tg->sendMessage([
                'chat_id' => $chatId,
                'text' => "Список команд:\n/start или /hi — приветствие\n/help — помощь\n/pfvtyf — скоро будет реализовано",
            ]),

            default => $tg->sendMessage([
                'chat_id' => $chatId,
                'text' => "Неизвестная команда. Введите /help для списка команд.",
            ]),
        };

        return success();
    }

    public function test(Request $request)
    {

        $chatId = 616322991;
//        $bot = Telegram::getMe();
//        Telegram::sendMessage([
//            'chat_id' => $chatId,
//            'text' => 'Привет!',
//        ]);

//        Telegram::sendMessage([
//            'chat_id' => $chatId,
//            'text' => 'Нажмите кнопку ниже:',
//            'reply_markup' => json_encode([
//                'keyboard' => [
//                    [['text' => '📞 Отправить номер', 'request_contact' => true]],
//                ],
//                'resize_keyboard' => true,
//                'one_time_keyboard' => true,
//            ]),
//        ]);

//        Telegram::sendMessage([
//            'chat_id' => $chatId,
//            'text' => 'Выберите:',
//            'reply_markup' => json_encode([
//                'inline_keyboard' => [
//                    [
//                        ['text' => 'Открыть сайт', 'url' => 'https://example.com'],
//                        ['text' => 'Поздороваться', 'callback_data' => 'hi'],
//                    ],
//                ],
//            ]),
//        ]);

        Telegram::sendPhoto([
            'chat_id' => -1002693142471,
            'photo' => fopen("https://tt.tishchenko.kiev.ua/storage/236/profile_picture.webp", 'r'),
            'caption' => "ТЕСТ!!! Привет, @olha_mo! Это изображение с веба ️",
        ]);


        return success();
    }

}
