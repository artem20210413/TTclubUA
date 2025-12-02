<?php

namespace App\Services\Telegram\Commands\Set;

use App\Enum\EnumTelegramLoggerDirection;
use App\Models\TelegramLogger;
use App\Models\TelegramMessage;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class CommandSetpassword implements InterfaceCommand
{
    /**
     * Первый шаг — запросить пароль
     */
    public static function action(TelegramMessageDto $dto): void
    {
        TelegramLogger::sendMessage([
            'chat_id' => $dto->getChat()->getId(),
            'text' =>
                "🔐 *Створення нового пароля*\n\n" .
//                "Будь ласка, введіть *новий пароль*.\n".
                "📌 *Вимоги до пароля:*\n" .
                "• мінімум 4 символів\n" .
//                "• не може складатися лише з цифр\n".
//                "• не повинен збігатися з вашим ім’ям або email\n".
//                "• бажано використовувати комбінацію літер, цифр та символів\n\n".

                "\nУ вас є *10 хвилин* для завершення операції." .
                "\n✏️ Введіть новий пароль:",
            'parse_mode' => 'Markdown',
        ]);

    }

    /**
     * Второй шаг — принять пароль, провалидировать и сохранить
     */
    public static function secondAction(TelegramMessageDto $dto, ?string $text): void
    {
        $chatId = $dto->getChat()->getId();
        $user = $dto->getUser();

        if (!$user) {
            TelegramLogger::sendMessage([
                'chat_id' => $chatId,
                'text' => "⚠️ Не можу змінити пароль — користувача не знайдено.",
            ]);
            return;
        }

        $password = trim((string)$text);

        if ($password === '') {
            TelegramLogger::sendMessage([
                'chat_id' => $chatId,
                'text' => "❗ Пароль не може бути порожнім.\n\nСпробуйте ще раз:",
            ]);
            return;
        }

        if (strlen($password) < 4) {
            TelegramLogger::sendMessage([
                'chat_id' => $chatId,
                'text' => "❗ Пароль повинен містити *мінімум 4 символів*.",
                'parse_mode' => 'Markdown',
            ]);
            return;
        }


        // -------------------
        // СОХРАНЕНИЕ ПАРОЛЯ
        // -------------------
        $user->setPassword($password);
        $user->save();


        // Удаляем пароль из чата и логов
        TelegramLogger::deleteMessage([
            'chat_id' => $chatId,
            'message_id' => $dto->getMessageId(),
        ]);

        TelegramMessage::getLast($chatId, EnumTelegramLoggerDirection::IN)?->delete();

        TelegramLogger::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ *Пароль успішно змінено!*\n\nМожете продовжувати користуватися ботом.",
            'parse_mode' => 'Markdown',
        ]);
    }
}
