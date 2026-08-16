<?php

namespace App\Console\Commands\Tg;

use App\Services\Telegram\MessagePinner;
use Illuminate\Console\Command;
use Telegram\Bot\Api;

/**
 * Manual verification helper (not part of the feature itself): sends a message to the
 * configured test group and pins it until an exact end time (or indefinitely), so pinning
 * and the tg:unpin-expired-messages sweep can be checked against the real Telegram API.
 */
class TestPinMessage extends Command
{
    protected $signature = 'tg:test-pin-message
        {minutes=2 : Minutes to keep it pinned; 0 means pin indefinitely}
        {--delete : When the pin expires, delete the message instead of just unpinning it}';

    protected $description = 'Send a test message to the test_bot_2 chat and pin it until now + N minutes (or forever)';

    public function handle(Api $telegram, MessagePinner $pinner): int
    {
        $chatId = config('telegram.chats.test_bot_2');

        if (empty($chatId)) {
            $this->error('telegram.chats.test_bot_2 is not configured.');

            return self::FAILURE;
        }

        $minutes = (int) $this->argument('minutes');
        $unpinAt = $minutes > 0 ? now()->addMinutes($minutes) : null;
        $deleteAfterUnpin = $this->option('delete');

        $response = $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $unpinAt
                ? "Тестове повідомлення: закріплення до {$unpinAt->toTimeString()}".($deleteAfterUnpin ? ', потім видалення' : '')
                : 'Тестове повідомлення: закріплення безстроково',
        ]);

        $messageId = $response->message_id;

        $pinner->pinUntil($chatId, $messageId, $unpinAt, true);

        $action = $deleteAfterUnpin ? 'deleted' : 'unpinned';
        $this->info("Sent and pinned message_id={$messageId} in chat {$chatId}, unpin_at=".($unpinAt ?? 'never (indefinite)'));
        $this->info("Wait for the duration to pass, then run: php artisan tg:unpin-expired-messages (it will be {$action}).");

        return self::SUCCESS;
    }
}
