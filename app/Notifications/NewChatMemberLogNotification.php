<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\Dto\TelegramWebhookDto;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class NewChatMemberLogNotification extends Notification
{
    public function __construct(private readonly TelegramWebhookDto $telegramWebhookDto) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        return new TelegramMessagePayload(
            text: TelegramBotHelpers::generationTextNewUserLog($this->telegramWebhookDto)
        );
    }
}
