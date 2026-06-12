<?php

namespace App\Services\Telegram;

use App\Eloquent\TelegramLoggerEloquent;
use App\Eloquent\UserEloquent;
use App\Enum\EnumTelegramEvents;
use App\Models\TelegramLogger;
use App\Models\TelegramMessage;
use App\Services\Telegram\Commands\Set\CommandContactSuccessfully;
use App\Services\Telegram\Commands\Set\CommandGetPhone;
use App\Services\Telegram\Commands\Set\CommandHelp;
use App\Services\Telegram\Commands\Set\CommandStart;
use App\Services\Telegram\Commands\Set\CommandUserNotActive;
use App\Services\Telegram\Commands\TelegramCommands;
use App\Services\Telegram\Dto\ChatMember\EnumTelegramChatMemberStatus;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\Dto\TelegramWebhookDto;
use Illuminate\Support\Facades\Log;

class TelegramActionPublicHandler
{

    protected TelegramMessage $telegramMessage;

    public function __construct(readonly TelegramWebhookDto $telegramWebhookDto)
    {

        $this->telegramMessage = TelegramLoggerEloquent::createIn($telegramWebhookDto);


    }

    public function handler()
    {
        if ($this->checkIsNewUser()) $this->handleNewUser();
        if ($this->checkIsUserLeft()) $this->handleUserLeft();
    }

    private function checkIsNewUser(): bool
    {
        return !empty($this->telegramWebhookDto?->getMessage()?->getNewChatMembers());
    }

    private function checkIsUserLeft(): bool
    {
        $status = $this->telegramWebhookDto?->getChatMember()?->getNewChatMember()?->getStatus() ?? null;

        return in_array($status, [EnumTelegramChatMemberStatus::LEFT, EnumTelegramChatMemberStatus::KICKED]);
    }

    private function handleNewUser()
    {

        try {
            // Відправка у чаи логов
            $messageLog = TelegramBotHelpers::generationTextNewUserLog($this->telegramWebhookDto);
            $bot = new TelegramBot(EnumTelegramEvents::CHANGE_USER);
            $res = $bot->sendMessage($messageLog);

            foreach ($this->telegramWebhookDto->getMessage()->getNewChatMembers() as $chatMember) {
                $user = UserEloquent::searchUser($chatMember);
                $user?->setAsActive(true);
            }


        } catch (\Throwable $exception) {
            Log::error("Помилка логування зміни статусу користувача: " . $exception->getMessage(), [
                'exception' => $exception,
                'chat_id' => $this->telegramWebhookDto->getSmartChat()?->getId()
            ]);
        }

        try {
            $text = TelegramBotHelpers::generationTextNewUser($this->telegramWebhookDto?->getMessage()->getNewChatMembers());
            $buttons = config('telegram.messages.new_member_welcome.links', []);

            $bot = new TelegramBot(EnumTelegramEvents::CUSTOM);
            $bot->setTelegramIds($this->telegramWebhookDto?->getSmartChat()->getId());
            $res = $bot->sendMessage($text, $buttons);
        } catch (\Throwable $exception) {
            Log::error("Помилка логування зміни статусу користувача: " . $exception->getMessage(), [
                'exception' => $exception,
                'chat_id' => $this->telegramWebhookDto->getSmartChat()?->getId()
            ]);
        }
    }

    private function handleUserLeft()
    {
        try {
            // Відправка у чаи логов
            $messageLog = TelegramBotHelpers::generationTextUserLeftLog($this->telegramWebhookDto);
            $bot = new TelegramBot(EnumTelegramEvents::CHANGE_USER);
            $res = $bot->sendMessage($messageLog);

            $user = UserEloquent::searchUser($this->telegramWebhookDto->getChatMember()->getNewChatMember()->getUser());
            $user?->setAsActive(false);

        } catch (\Throwable $exception) {
            Log::error("Помилка логування зміни статусу користувача: " . $exception->getMessage(), [
                'exception' => $exception,
                'chat_id' => $this->telegramWebhookDto->getSmartChat()?->getId()
            ]);
        }
    }


}
