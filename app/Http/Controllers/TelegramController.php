<?php

namespace App\Http\Controllers;

use App\Eloquent\UserEloquent;
use App\Enum\EnumTelegramEvents;
use App\Http\Controllers\Api\ApiException;
use App\Notifications\AdHocMessageNotification;
use App\Notifications\Support\TelegramMessagePayload;
use App\Notifications\Support\TelegramRecipients;
use App\Services\Telegram\Dto\TelegramWebhookDto;
use App\Services\Telegram\TelegramActionPublicHandler;
use App\Services\Telegram\TelegramCommandHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {

        // https://api.telegram.org/bot<BOT_TOKEN>/setWebhook?url=https://tt.tishchenko.kiev.ua/api/telegram/webhook
        Log::info('webhook request received', [$request->all()]);

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
            Notification::send(
                TelegramRecipients::routes([$telegramWebhookDto?->getSmartChat()?->getId() ?? null]),
                new AdHocMessageNotification(new TelegramMessagePayload(text: $e->getMessage()))
            );
        } catch (\Throwable $e) {
            Log::error("TG webhook. {$e->getMessage()}");
            //            throw $e;
        }

        return success();
    }

    public function test(Request $request)
    {
        Notification::send(
            TelegramRecipients::routes(EnumTelegramEvents::TEST->getIds()),
            new AdHocMessageNotification(new TelegramMessagePayload(text: 'LIST_BIRTHDAYS'))
        );

        return success();
    }
}
