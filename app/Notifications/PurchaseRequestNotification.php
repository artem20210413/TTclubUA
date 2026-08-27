<?php

namespace App\Notifications;

use App\Enum\EnumTypeMedia;
use App\Models\Goods;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class PurchaseRequestNotification extends Notification
{
    public function __construct(
        private readonly User $user,
        private readonly Goods $goods,
        private readonly ?string $description = null,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $text = TelegramBotHelpers::renderTemplate('purchase_request', [
            '{user}' => TelegramBotHelpers::TryMentionPerson($this->user),
            '{name}' => $this->user->name,
            '{phone}' => $this->user->phone ?? '-',
            '{title}' => $this->goods->title,
            '{price}' => $this->goods->price ?? '-',
            '{inactive_line}' => $this->goods->active ? '' : "⚠️ <b>Товар неактивний</b>\n",
            '{description}' => $this->description ?? '-',
        ]);
        $imageUrl = app()->environment('local') ? null : $this->goods->getFirstMediaUrl(EnumTypeMedia::PHOTO_GOODS->value);

        if (! empty($imageUrl)) {
            return new TelegramMessagePayload(text: $text, mediaGroup: [$imageUrl]);
        }

        return new TelegramMessagePayload(text: $text);
    }
}
