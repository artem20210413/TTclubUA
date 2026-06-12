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
use App\Models\TelegramLogger;
use App\Models\User;
use App\Services\Telegram\Dto\TelegramImportDto;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\Dto\TelegramWebhookDto;
use App\Services\Telegram\TelegramActionPublicHandler;
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


//        $message = $request->message ?? $request->edited_message ?? null;
//        if (!$message) return success();

        $telegramWebhookDto = new TelegramWebhookDto($request->all() ?? []);


        try {
            if ($telegramMessageDto = $telegramWebhookDto->getMessage()) {
                $user = UserEloquent::updateByTg($telegramMessageDto);
                $telegramMessageDto->setUser($user);

                if ($telegramMessageDto->getChat()->getType() === 'private') {
                    new TelegramCommandHandler($telegramWebhookDto);
                }
//                else {
//                    new TelegramCommandPublicHandler($telegramMessageDto);
//                }
            }

            $action = new TelegramActionPublicHandler($telegramWebhookDto);
            $action->handler();
        } catch (ApiException $e) {
            TelegramLogger::sendMessage([
                'chat_id' => $telegramWebhookDto?->getSmartChat()?->getId() ?? null,
                'text' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error("TG webhook. {$e->getMessage()}");
            throw $e;
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
