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
        $who = $this->telegramWebhookDto->getMessage()->getFrom();
        $whom = [];
        foreach ($this->telegramWebhookDto->getMessage()->getNewChatMembers() as $member) {
            $whom[] = "<a href='tg://user?id={$member->getId()}'>{$member->getFirstName()}</a>";
        }
        $where = $this->telegramWebhookDto->getSmartChat()->getSmartTitle();

        $text = TelegramBotHelpers::renderTemplate('new_member_welcome_log', [
            '{who}' => "<a href='tg://user?id={$who->getId()}'>{$who->getFirstName()}</a>",
            '{whom}' => implode(', ', $whom),
            '{where}' => $where,
        ], 'generationTextNewUserLog');

        return new TelegramMessagePayload(text: $text);
    }
}
