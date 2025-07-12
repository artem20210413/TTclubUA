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

}
