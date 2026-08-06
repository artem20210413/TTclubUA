<?php

namespace App\Services\Telegram\Commands\Set;

use App\Eloquent\TelegramLoggerEloquent;
use App\Enum\EnumTelegramLoggerDirection;
use App\Models\TelegramMessage;
use App\Notifications\CommandReplyNotification;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Telegram\Bot\Api;

class CommandSetpassword implements InterfaceCommand
{
    /**
     * Первый шаг — запросить пароль
     */
    public static function action(TelegramMessageDto $dto): void
    {
        Notification::route('telegram', $dto->getChat()->getId())->notify(
            new CommandReplyNotification('commands.set_password.prompt')
        );
    }

    /**
     * Второй шаг — принять пароль, провалидировать и сохранить
     */
    public static function secondAction(TelegramMessageDto $dto, ?string $text): void
    {
        $chatId = $dto->getChat()->getId();
        $user = $dto->getUser();

        if (! $user) {
            Notification::route('telegram', $chatId)->notify(
                new CommandReplyNotification('commands.set_password.no_user')
            );

            return;
        }

        $password = trim((string) $text);

        if ($password === '') {
            Notification::route('telegram', $chatId)->notify(
                new CommandReplyNotification('commands.set_password.empty')
            );

            return;
        }

        if (strlen($password) < 4) {
            Notification::route('telegram', $chatId)->notify(
                new CommandReplyNotification('commands.set_password.too_short')
            );

            return;
        }

        // -------------------
        // СОХРАНЕНИЕ ПАРОЛЯ
        // -------------------
        $user->setPassword($password);
        $user->save();

        // Удаляем пароль из чата и логов
        self::deleteMessage($chatId, $dto->getMessageId());

        TelegramMessage::getLast($chatId, EnumTelegramLoggerDirection::IN)?->delete();

        Notification::route('telegram', $chatId)->notify(
            new CommandReplyNotification('commands.set_password.success')
        );
    }

    private static function deleteMessage(string|int $chatId, mixed $messageId): void
    {
        $params = ['chat_id' => $chatId, 'message_id' => $messageId];

        try {
            app(Api::class)->deleteMessage($params);
            TelegramLoggerEloquent::createOutDelete($params);
        } catch (\Throwable $e) {
            Log::error('Telegram deleteMessage error: '.$e->getMessage());
            TelegramLoggerEloquent::createOutDelete($params);
        }
    }
}
