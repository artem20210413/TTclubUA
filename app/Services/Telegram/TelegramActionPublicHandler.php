<?php

namespace App\Services\Telegram;

use App\Eloquent\TelegramLoggerEloquent;
use App\Eloquent\UserEloquent;
use App\Enum\EnumTelegramEvents;
use App\Models\TelegramMessage;
use App\Notifications\ChatMemberLeftNotification;
use App\Notifications\NewChatMemberLogNotification;
use App\Notifications\NewChatMemberWelcomeNotification;
use App\Notifications\Support\TelegramRecipients;
use App\Services\Telegram\Dto\ChatMember\EnumTelegramChatMemberStatus;
use App\Services\Telegram\Dto\TelegramWebhookDto;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TelegramActionPublicHandler
{
    protected ?TelegramMessage $telegramMessage;

    public function __construct(public readonly TelegramWebhookDto $telegramWebhookDto)
    {

        $this->telegramMessage = TelegramLoggerEloquent::createIn($telegramWebhookDto);

    }

    public function handler()
    {
        if ($this->checkIsNewUser()) {
            $this->handleNewUser();
        }
        if ($this->checkIsUserLeft()) {
            $this->handleUserLeft();
        }
    }

    private function checkIsNewUser(): bool
    {
        return ! empty($this->telegramWebhookDto?->getMessage()?->getNewChatMembers());
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
            Notification::send(
                TelegramRecipients::routes(EnumTelegramEvents::CHANGE_USER->getIds()),
                new NewChatMemberLogNotification($this->telegramWebhookDto)
            );

            foreach ($this->telegramWebhookDto->getMessage()->getNewChatMembers() as $chatMember) {
                $user = UserEloquent::searchUser($chatMember);
                $user?->setAsActive(true);
            }

        } catch (\Throwable $exception) {
            Log::error('Помилка логування зміни статусу користувача: '.$exception->getMessage(), [
                'exception' => $exception,
                'chat_id' => $this->telegramWebhookDto->getSmartChat()?->getId(),
            ]);
        }

        try {
            Notification::send(
                TelegramRecipients::routes([$this->telegramWebhookDto?->getSmartChat()->getId()]),
                new NewChatMemberWelcomeNotification($this->telegramWebhookDto?->getMessage()->getNewChatMembers())
            );
        } catch (\Throwable $exception) {
            Log::error('Помилка логування зміни статусу користувача: '.$exception->getMessage(), [
                'exception' => $exception,
                'chat_id' => $this->telegramWebhookDto->getSmartChat()?->getId(),
            ]);
        }
    }

    private function handleUserLeft()
    {
        try {
            // Відправка у чаи логов
            Notification::send(
                TelegramRecipients::routes(EnumTelegramEvents::CHANGE_USER->getIds()),
                new ChatMemberLeftNotification($this->telegramWebhookDto)
            );

            $user = UserEloquent::searchUser($this->telegramWebhookDto->getChatMember()->getNewChatMember()->getUser());
            $user?->setAsActive(false);

        } catch (\Throwable $exception) {
            Log::error('Помилка логування зміни статусу користувача: '.$exception->getMessage(), [
                'exception' => $exception,
                'chat_id' => $this->telegramWebhookDto->getSmartChat()?->getId(),
            ]);
        }
    }
}
