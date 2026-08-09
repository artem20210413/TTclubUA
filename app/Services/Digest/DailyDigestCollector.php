<?php

namespace App\Services\Digest;

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
        $sourceChats = array_values(array_filter((array) config('telegram.digest.source_chats')));

        if (empty($sourceChats)) {
            return [];
        }

        $texts = TelegramMessage::query()
            ->where('direction', EnumTelegramLoggerDirection::IN->value)
            ->whereIn('chat_id', $sourceChats)
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->whereNotNull('text')
            ->orderBy('id')
            ->pluck('text');

        $seen = [];
        $result = [];

        foreach ($texts as $text) {
            $clean = trim((string) $text);

            if ($clean === '') {
                continue;
            }

            $clean = mb_substr($clean, 0, self::MAX_MESSAGE_LENGTH);

            $key = mb_strtolower($clean);
            if (isset($seen[$key])) {
                continue; // dedupe repeated lines
            }
            $seen[$key] = true;

            $result[] = $clean;

            if (count($result) >= self::MAX_MESSAGES) {
                break;
            }
        }

        return $result;
    }
}
