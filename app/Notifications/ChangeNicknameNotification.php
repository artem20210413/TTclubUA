<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class ChangeNicknameNotification extends Notification
{
    public function __construct(
        private readonly ?string $oldNickname,
        private readonly ?string $newNickname,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $text = TelegramBotHelpers::renderTemplate('change_nickname', [
            '{old_nickname}' => $this->oldNickname ?? 'відсутнього',
            '{new_nickname}' => $this->newNickname ?? 'порожній',
        ], 'Нікнейм оновлено: {old_nickname} -> {new_nickname}');

        return new TelegramMessagePayload(text: $text);
    }
}
