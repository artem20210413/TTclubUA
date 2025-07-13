<?php

namespace App\Services\Telegram;

use App\Models\User;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramCommandHandler
{
    public function __construct(readonly array $message, readonly int $chatId, readonly ?User $user)
    {
        $text = $message['text'] ?? '';

        if (!$user) return $this->commandGetPhone();
        if (!$user->active) return $this->commandUserNotActive();
        if (isset($message['contact'])) return $this->commandContactSuccessfully();
//
//        if (!$this->user) {
//            $this->commandGetPhone();
//            return;
//        }
//        if (!$this->user->active) {
//            $this->commandUserNotActive();
//            return;
//        }
//
//        $contact = $message['contact'] ?? null;
//        if ($contact) {
//            $this->commandContactSuccessfully();
//            return;
//        }

        $this->handleCommand($text);
    }

    private function handleCommand(string $text): void
    {
        $pieces = explode(' ', trim($text));
        $command = $pieces[0] ?? '';

        match ($command) {
            '/start', '/hi' => $this->commandStart(),
            '/changePassword', '/CP' => $this->commandChangePassword($pieces[1] ?? null),
            '/help' => $this->commandHelp(),
            default => $this->commandDefault(),
        };
    }


    public function commandHelp()
    {
        $text = "🆘 *Допомога — список команд:*
                \n/start або /hi — привітання з ботом 🤖
                \n/help — показати це повідомлення з переліком команд 📋
                \n/changePassword {new-password} або /CP {new-password} — змінити пароль до вашого акаунта 🔐
                \nБільше можливостей з'явиться скоро. Якщо виникли питання — звертайтесь до підтримки.";

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
            'text' => "Привіт {$this->user->name}!
            Я Telegram-бот Клубу TT.
            \nЩоб дізнатися що я вмію напиши команду '/help'",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [
                        ['text' => '/start'],
                        ['text' => '/help'],
                        ['text' => '/changePassword'],
                    ],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);

    }

    public function commandGetPhone()
    {
        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => "👋 Привіт!\n\nДля продовження спілкування з ботом необхідно ідентифікувати себе.
            \n\nНатисни кнопку *«📞 Надіслати номер»* нижче, щоб поділитися своїм номером телефону. Це потрібно лише один раз і дозволить нам впевнено знати, хто ти 😊",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => '📞 Надіслати номер', 'request_contact' => true]],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true, // клавиатура исчезнет после нажатия
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
        $this->commandStart();
    }

    public function commandUserNotActive()
    {
        Telegram::sendMessage([
            'chat_id' => $this->chatId,
            'text' => "⚠️ Ваш обліковий запис неактивний.\n\nЗверніться до адміністратора або служби підтримки, щоб активувати доступ.",
        ]);
    }

    public function commandChangePassword(?string $password)
    {
        if (!$password) {
            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => "❗ Пароль не було вказано після команди",
            ]);
            return;
        }

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
