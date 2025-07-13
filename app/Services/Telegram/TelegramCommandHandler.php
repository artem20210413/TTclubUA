<?php

namespace App\Services\Telegram;

use App\Models\User;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramCommandHandler
{
    public function __construct(readonly array $message, readonly int $chatId, readonly ?User $user)
    {
        $text = $message['text'] ?? '';

        if (!$this->user) {
            $this->commandGetPhone();
            return;
        }

        $contact = $message['contact'] ?? null;
        if ($contact) {
            $this->commandContactSuccessfully();
            return;
        }


        $pieces = explode(' ', trim($text));

        match ($pieces[0] ?? '') {
            '/start', '/hi' => $this->commandStart(),

            '/change-password' => $this->commandChangePassword($pieces[1] ?? null),

            '/help' => $this->commandHelp(),

            default => $this->commandDefault()
        };
    }


    public function commandHelp()
    {
        $text = <<<TEXT
🆘 *Допомога — список команд:*

/start або /hi — привітання з ботом 🤖

/help — показати це повідомлення з переліком команд 📋

/change-password — змінити пароль до вашого акаунта 🔐

Більше можливостей з'явиться скоро. Якщо виникли питання — звертайтесь до підтримки.
TEXT;

        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }

    public function commandStart()
    {
        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => "Привіт {$this->user->name}! Я Telegram-бот Клубу TT. Щоб дізнатися що я вмію напиши команду '/help'",
        ]);

    }

    public function commandGetPhone()
    {
        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => 'Для продовження спілкування з ботом необхідно ідентифікувати себе',
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => '📞 Надіслати номер', 'request_contact' => true]],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    public function commandContactSuccessfully()
    {
        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => "✅ Дякуємо {$this->user->name}!\nМожемо продовжити спілкування 👌",
            'reply_markup' => json_encode([
                'remove_keyboard' => true, // убрать клавиатуру
            ]),
        ]);
    }

    public function commandChangePassword(string $password)
    {
        $password = trim($password);

        if (strlen($password) < 8) {
            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => "❗ Пароль має містити щонайменше 8 символів.",
            ]);
            return;
        }
        // Сменить пароль
        $this->user->setPassword($password);
        $this->user->save();

        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => "✅ Пароль успішно змінено.",
        ]);
    }

    public function commandDefault()
    {
        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => "🤖 Невідома команда. Введіть /help, щоб переглянути список доступних команд.",
        ]);
    }


}
