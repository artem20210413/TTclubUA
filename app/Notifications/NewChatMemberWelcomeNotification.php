<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class NewChatMemberWelcomeNotification extends Notification
{
    public function __construct(private readonly array $newChatMembers) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        return new TelegramMessagePayload(
            text: TelegramBotHelpers::generationTextNewUser($this->newChatMembers),
            buttons: TelegramBotHelpers::getNewMemberWelcomeLinks(),
        );
    }
}
