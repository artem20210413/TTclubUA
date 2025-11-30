<?php

namespace App\Http\Controllers;

use App\Eloquent\FinanceEloquent;
use App\Eloquent\UserEloquent;
use App\Enum\EnumMonoAccount;
use App\Enum\EnumMonoStatus;
use App\Enum\EnumTelegramEvents;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\FinanceRequest;
use App\Http\Resources\FinanceWithUserResource;
use App\Models\Finance;
use App\Models\MonoTransaction;
use App\Models\User;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramCommandHandler;
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

        $message = $request->message ?? $request->edited_message ?? null;
        if (!$message) return success();
        $chatId = $message['chat']['id'];
        $chatType = $message['chat']['type']??null;
        $isPrivet = $chatType=== 'private';
//        $fromId = $message['from']['id'];
        if (!$isPrivet) return success();

        $contact = $message['contact'] ?? null;

        try {
            $user = UserEloquent::updateByTg($message['from'], $contact);
            new TelegramCommandHandler($message, $chatId, $user);
        } catch (ApiException $e) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❗ Виникла непередбачена помилка.\nЧас: " . now()->format('Y-m-d H:i:s') . "\n\nБудь ласка, спробуйте пізніше або зверніться до підтримки.",
            ]);
        }

        return success();
    }

    public function test(Request $request)
    {

        $bot = new TelegramBot(EnumTelegramEvents::TEST);
        $bot->sendMessage('LIST_BIRTHDAYS');

        return success();
    }

}
