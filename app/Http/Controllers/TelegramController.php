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
//        dd($request->all());
        //https://api.telegram.org/bot<BOT_TOKEN>/setWebhook?url=https://tt.tishchenko.kiev.ua/api/telegram/webhook
        Log::info("webhook request received", [$request->all()]);
//        Log::info("webhook headers received", [$request->header()]);

        $message = $request->message ?? $request->edited_message ?? null;
        if (!$message) return success();
        $telegramMessageDto = new TelegramMessageDto($request->all()['message'] ?? []);

        if ($telegramMessageDto->getChat()->getType() !== 'private') return success();

        try {
            new TelegramCommandHandler($telegramMessageDto);
        } catch (ApiException $e) {
            TelegramLogger::sendMessage([
                'chat_id' => $telegramMessageDto->getChat()->getId(),
                'text' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            TelegramLogger::sendMessage([
                'chat_id' => $telegramMessageDto->getChat()->getId(),
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
