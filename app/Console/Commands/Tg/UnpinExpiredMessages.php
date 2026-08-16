<?php

namespace App\Console\Commands\Tg;

use App\Models\PinnedMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class UnpinExpiredMessages extends Command
{
    protected $signature = 'tg:unpin-expired-messages';

    protected $description = 'Unpin Telegram messages whose pinned_messages.unpin_at has passed (runs every 5 minutes)';

    public function handle(Api $telegram): int
    {
        $expired = PinnedMessage::query()
            ->whereNotNull('unpin_at')
            ->where('unpin_at', '<=', now())
            ->get();

        foreach ($expired as $pinnedMessage) {
            try {
                if ($pinnedMessage->delete_after_unpin) {
                    // Deleting the message also removes its pin, so there is no separate unpin call.
                    $telegram->deleteMessage([
                        'chat_id' => $pinnedMessage->chat_id,
                        'message_id' => $pinnedMessage->message_id,
                    ]);
                } else {
                    $telegram->unpinChatMessage([
                        'chat_id' => $pinnedMessage->chat_id,
                        'message_id' => $pinnedMessage->message_id,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Telegram unpin/delete error: '.$e->getMessage(), [
                    'chat_id' => $pinnedMessage->chat_id,
                    'message_id' => $pinnedMessage->message_id,
                ]);
            }

            $pinnedMessage->delete();
        }

        $this->info("Unpinned {$expired->count()} message(s).");

        return self::SUCCESS;
    }
}
