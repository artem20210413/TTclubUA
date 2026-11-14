<?php

namespace App\Services\Telegram;

use App\Eloquent\TelegramLoggerEloquent;
use App\Eloquent\UserEloquent;
use App\Enum\EnumTelegramEvents;
use App\Models\TelegramMessage;
use App\Models\User;
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
        $chatId = $this->telegramWebhookDto->getSmartChat()?->getId();

        // Вступ/вихід впливає на активність акаунта лише в головній групі клубу. Інакше будь-хто
        // міг би додати бота у свою групу й через вступ/вихід (де)активувати членів клубу.
        $isMembershipChat = EnumTelegramEvents::MEMBERSHIP->hasChat($chatId);

        // Привітання — тільки в чатах з білого списку, щоб бот не вітав людей у чужих групах.
        $isWelcomeChat = EnumTelegramEvents::NEW_MEMBER_WELCOME->hasChat($chatId);

        if ($this->checkIsNewUser()) {
            if ($isMembershipChat) {
                $this->handleNewUser();
            }
            if ($isWelcomeChat) {
                $this->sendWelcome();
            }
        }

        if ($this->checkIsUserLeft() && $isMembershipChat) {
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
    }

    private function sendWelcome(): void
    {
        try {
            Notification::send(
                TelegramRecipients::routes([$this->telegramWebhookDto?->getSmartChat()->getId()]),
                new NewChatMemberWelcomeNotification($this->telegramWebhookDto?->getMessage()->getNewChatMembers())
            );
        } catch (\Throwable $exception) {
            Log::error('Помилка надсилання привітання новому учаснику: '.$exception->getMessage(), [
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

            $member = $this->telegramWebhookDto->getChatMember()->getNewChatMember()->getUser();

            // Шукаємо за telegram_id
            $user = UserEloquent::searchUser($member);

            // Фолбек: шукаємо за ніком у ТГ, якщо за telegram_id не знайшли
            if (! $user && $member?->getUsername()) {
                $user = User::query()->where('telegram_nickname', $member->getUsername())->first();
            }

            if ($user) {
                $user->setAsActive(false);
            } else {
                Log::warning('Користувач, що покинув чат, не знайдений у БД — деактивацію пропущено', [
                    'telegram_id' => $member?->getId(),
                    'telegram_nickname' => $member?->getUsername(),
                    'chat_id' => $this->telegramWebhookDto->getSmartChat()?->getId(),
                ]);
            }

        } catch (\Throwable $exception) {
            Log::error('Помилка логування зміни статусу користувача: '.$exception->getMessage(), [
                'exception' => $exception,
                'chat_id' => $this->telegramWebhookDto->getSmartChat()?->getId(),
            ]);
        }
    }
}
