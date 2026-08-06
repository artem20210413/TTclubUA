<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\Dto\ChatMember\EnumTelegramChatMemberStatus;
use App\Services\Telegram\Dto\TelegramWebhookDto;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class ChatMemberLeftNotification extends Notification
{
    public function __construct(private readonly TelegramWebhookDto $telegramWebhookDto) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $key = $this->telegramWebhookDto->getChatMember()->getNewChatMember()->getStatus() === EnumTelegramChatMemberStatus::LEFT
            ? 'member_left_log'
            : 'member_kicked_log';

        $who = $this->telegramWebhookDto->getChatMember()->getFromUser();
        $member = $this->telegramWebhookDto->getChatMember()->getNewChatMember()->getUser();
        $whom = "<a href='tg://user?id={$member->getId()}'>{$member->getFirstName()}</a>";
        $where = $this->telegramWebhookDto->getSmartChat()->getSmartTitle();

        $text = TelegramBotHelpers::renderTemplate($key, [
            '{who}' => "<a href='tg://user?id={$who->getId()}'>{$who->getFirstName()}</a>",
            '{whom}' => $whom,
            '{where}' => $where,
        ], 'generationTextNewUserLog');

        return new TelegramMessagePayload(text: $text);
    }
}
