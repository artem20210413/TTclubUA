<?php

namespace App\Services\Telegram;

use App\Eloquent\TelegramLoggerEloquent;
use App\Models\TelegramLogger;
use App\Models\TelegramMessage;
use App\Models\User;
use App\Services\Telegram\Dto\TelegramMessageDto;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramCommandHandler
{

    protected TelegramMessage $telegramMessage;

    public function __construct(readonly TelegramMessageDto $telegramMessageDto)
    {

        $this->telegramMessage = TelegramLoggerEloquent::createIn($telegramMessageDto);
        $text = $telegramMessageDto->getText() ?? '';

        match (true) {
            !$this->telegramMessageDto->getUser() => $this->commandGetPhone(),
            !$this->telegramMessageDto->getUser()->active => $this->commandUserNotActive(),
            $this->telegramMessageDto->getContact() !== null => $this->commandContactSuccessfully(),
            default => $this->handleCommand($text)
        };

    }

    private function handleCommand(string $text): void
    {
        $pieces = explode(' ', trim($text));
        $command = $pieces[0] ?? '';

        match ($command) {
            '/start', '/hi' => $this->commandStart(),
            //TODO '/changePassword', '/CP' => $this->commandChangePassword($pieces[1] ?? null),
            '/help' => $this->commandHelp(),
            default => $this->commandDefault(),
        };

        //TODO если предыдущее сообщение от пользователя была смена пароля и не прошло больше 10 минут и не совпадает ниодной команде, то меняем пароль и удаляем telegramMessage
    }


    public function commandHelp()
    {
        $text = "🆘 *Допомога — список команд:*
                \n/start або /hi — привітання з ботом 🤖
                \n/help — показати це повідомлення з переліком команд 📋
                \n/changePassword {new-password} або /CP {new-password} — змінити пароль до вашого акаунта 🔐
                \nБільше можливостей з'явиться скоро. Якщо виникли питання — звертайтесь до підтримки.";

        TelegramLogger::sendMessage([
            'chat_id' => $this->telegramMessageDto->getChat()->getId(),
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }

    public function commandStart()
    {
        TelegramLogger::sendMessage([
            'chat_id' => $this->telegramMessageDto->getChat()->getId(),
            'text' => "Привіт {$this->telegramMessageDto->getUser()->name}!\nЯ Telegram-бот Клубу TT.\nЩоб дізнатися що я вмію напиши команду '/help'",
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

    public function commandGetPhone(): void
    {
        TelegramLogger::sendMessage([
            'chat_id' => $this->telegramMessageDto->getChat()->getId(),
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

    public function commandContactSuccessfully(): void
    {
        TelegramLogger::sendMessage([
            'chat_id' => $this->telegramMessageDto->getChat()->getId(),
            'text' => "✅ Дякуємо {$this->telegramMessageDto->getUser()->name}!\nМожемо продовжити спілкування 👌",
            'reply_markup' => json_encode([
                'remove_keyboard' => true, // убрать клавиатуру
            ]),
        ]);
        $this->commandStart();
    }

    public function commandUserNotActive(): void
    {
        TelegramLogger::sendMessage([
            'chat_id' => $this->telegramMessageDto->getChat()->getId(),
            'text' => "⚠️ Ваш обліковий запис неактивний.\n\nЗверніться до адміністратора або служби підтримки, щоб активувати доступ.",
        ]);
    }

    public function commandChangePassword(?string $password)
    {
        if (!$password) {
            TelegramLogger::sendMessage([
                'chat_id' => $this->telegramMessageDto->getChat()->getId(),
                'text' => "❗ Пароль не було вказано після команди",
            ]);
            return;
        }

        $password = trim($password);

        if (strlen($password) < 4) {
            TelegramLogger::sendMessage([
                'chat_id' => $this->telegramMessageDto->getChat()->getId(),
                'text' => "❗ Пароль має містити щонайменше 4 символів.",
            ]);
            return;
        }
        // Сменить пароль
        $user = $this->telegramMessageDto->getUser();
        $user->setPassword($password);
        $user->save();

        TelegramLogger::sendMessage([
            'chat_id' => $this->telegramMessageDto->getChat()->getId(),
            'text' => "✅ Пароль успішно змінено.",
        ]);
    }

    public function commandDefault()
    {
        TelegramLogger::sendMessage([
            'chat_id' => $this->telegramMessageDto->getChat()->getId(),
            'text' => "🤖 Невідома команда. Введіть /help, щоб переглянути список доступних команд.",
        ]);
    }


}
