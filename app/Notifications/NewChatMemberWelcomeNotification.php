<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\Dto\TelegramUserDto;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

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
            text: $this->generationText(),
            buttons: $this->welcomeLinks(),
        );
    }

    private function welcomeLinks(): array
    {
        return Lang::has('telegram.new_member_welcome.links') ? __('telegram.new_member_welcome.links') : [];
    }

    private function generationText(): string
    {
        foreach ($this->newChatMembers as $member) {
            /** @var TelegramUserDto $member */
            return TelegramBotHelpers::renderTemplate('new_member_welcome.text', [
                '{member}' => "<a href='tg://user?id={$member->getId()}'>{$member->getFirstName()}</a>",
            ]);
        }

        return '';
    }
}
