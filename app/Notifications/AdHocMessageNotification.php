<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use Illuminate\Notifications\Notification;

/**
 * Generic Telegram notification for call sites whose message doesn't warrant
 * a dedicated Notification subclass (matches CommandReplyNotification's role
 * for bot commands — one reusable class, no per-site loop/logging duplication).
 */
class AdHocMessageNotification extends Notification
{
    public function __construct(private readonly TelegramMessagePayload $payload) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        return $this->payload;
    }
}
