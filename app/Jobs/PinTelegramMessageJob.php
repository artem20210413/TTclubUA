<?php

namespace App\Jobs;

use App\Services\Telegram\MessagePinner;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Pins one already-sent Telegram message until $unpinAt (null = indefinitely). Dispatched
 * right after a message is sent, with everything it needs passed in directly — no events,
 * no listeners.
 */
class PinTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string|int $chatId,
        public readonly string|int $messageId,
        public readonly ?Carbon $unpinAt,
        public readonly bool $deleteAfterUnpin = false,
        public readonly bool $notify = true,
    ) {}

    public function handle(MessagePinner $pinner): void
    {
        $pinner->pinUntil($this->chatId, $this->messageId, $this->unpinAt, $this->deleteAfterUnpin, $this->notify);
    }
}
