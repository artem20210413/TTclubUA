<?php

namespace App\Services\Digest;

use App\Enum\EnumTelegramEvents;
use App\Enum\EnumTelegramLoggerDirection;
use App\Models\TelegramMessage;
use Carbon\CarbonInterface;

/**
 * Gathers a day's incoming chat messages from the configured source chats,
 * trimmed and deduped, ready to feed the summarizer with minimal tokens (FR-011).
 */
class DailyDigestCollector
{
    /** Cap on messages fed to the AI to keep token usage bounded. */
    private const MAX_MESSAGES = 400;

    /** Per-message character cap. */
    private const MAX_MESSAGE_LENGTH = 500;

    /**
     * @return array<int, string>
     */
    public function forDate(CarbonInterface $date): array
    {
        $sourceChats = array_values(array_filter(EnumTelegramEvents::DAILY_DIGEST_COLLECT->getIds()));

        if (empty($sourceChats)) {
            return [];
        }

        $messages = TelegramMessage::query()
            ->where('direction', EnumTelegramLoggerDirection::IN->value)
            ->whereIn('chat_id', $sourceChats)
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->whereNotNull('text')
            ->orderBy('id')
            ->get(['text', 'raw', 'chat_id', 'message_id']);

        $seen = [];
        $result = [];

        foreach ($messages as $message) {
            $text = trim((string) $message->text);

            if ($text === '') {
                continue;
            }

            $text = mb_substr($text, 0, self::MAX_MESSAGE_LENGTH);

            // Prefix with the author's @username (when present) so the summary can tag people.
            $author = $this->authorHandle($message->raw);
            $clean = $author ? "{$author}: {$text}" : $text;

            // Add reply context so the AI understands what a message is answering.
            if ($reply = $this->replyContext($message->raw)) {
                $clean .= " {$reply}";
            }

            $key = mb_strtolower($clean);
            if (isset($seen[$key])) {
                continue; // dedupe repeated lines (before appending the unique link)
            }
            $seen[$key] = true;

            // Append a jump link to the exact message so the summary can point to it.
            if ($link = $this->messageLink($message->chat_id, $message->message_id)) {
                $clean .= " 🔗{$link}";
            }

            $result[] = $clean;

            if (count($result) >= self::MAX_MESSAGES) {
                break;
            }
        }

        return $result;
    }

    /**
     * Extract the message author's @username from the stored raw webhook payload.
     */
    private function authorHandle(mixed $raw): ?string
    {
        $username = data_get($raw, 'message.from.username');

        return $username ? '@'.$username : null;
    }

    /**
     * Build a compact "in reply to" hint from raw.message.reply_to_message so the
     * summary understands the thread. Returns null when the message is not a reply.
     */
    private function replyContext(mixed $raw): ?string
    {
        $replyText = trim((string) data_get($raw, 'message.reply_to_message.text'));

        if ($replyText === '') {
            return null;
        }

        $replyAuthor = data_get($raw, 'message.reply_to_message.from.username');
        $who = $replyAuthor ? '@'.$replyAuthor : 'повідомлення';
        $quote = mb_substr($replyText, 0, 120);

        return "(↩ у відповідь {$who}: \"{$quote}\")";
    }

    /**
     * Build a deep link to a specific supergroup message (t.me/c/<internal>/<id>).
     * Only supergroup chat ids (prefixed with -100) can be linked this way.
     */
    private function messageLink(mixed $chatId, mixed $messageId): ?string
    {
        $chatId = (string) $chatId;
        $messageId = (string) $messageId;

        if ($messageId === '' || ! str_starts_with($chatId, '-100')) {
            return null;
        }

        return 'https://t.me/c/'.substr($chatId, 4).'/'.$messageId;
    }
}
