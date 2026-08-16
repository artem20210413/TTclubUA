<?php

namespace App\Services\Telegram;

use App\Models\PinnedMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

/**
 * Pins a Telegram message, optionally for a limited number of hours, independent of any caller.
 * A scheduled sweep (tg:unpin-expired-messages) unpins records whose unpin_at has passed.
 */
class MessagePinner
{
    public function __construct(private readonly Api $telegram) {}

    /**
     * Pins a message until $unpinAt (null = indefinitely). The scheduled sweep
     * (tg:unpin-expired-messages) unpins/deletes it once $unpinAt passes.
     *
     * When $deleteAfterUnpin is true, the sweep deletes the Telegram message instead of just
     * unpinning it once $unpinAt passes.
     */
    public function pinUntil(string|int $chatId, string|int $messageId, ?Carbon $unpinAt, bool $deleteAfterUnpin = false): void
    {
        try {
            $this->telegram->pinChatMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
        } catch (\Throwable $e) {
            Log::error('Telegram pin error: '.$e->getMessage(), [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);

            return;
        }

        PinnedMessage::updateOrCreate(
            ['chat_id' => $chatId, 'message_id' => $messageId],
            ['unpin_at' => $unpinAt, 'delete_after_unpin' => $deleteAfterUnpin],
        );
    }
}
