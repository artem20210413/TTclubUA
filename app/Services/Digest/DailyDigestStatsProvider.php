<?php

namespace App\Services\Digest;

use App\Enum\EnumTelegramEvents;
use App\Enum\EnumTelegramLoggerDirection;
use App\Models\TelegramMessage;
use Carbon\CarbonInterface;

/**
 * Computes lightweight activity stats for the day: total messages and the most
 * active participants, formatted as a compact block for the digest.
 */
class DailyDigestStatsProvider
{
    private const TOP_LIMIT = 3;

    /**
     * @return array{total: int, top: array<string, int>, text: string}
     */
    public function forDate(CarbonInterface $date): array
    {
        $empty = ['total' => 0, 'top' => [], 'text' => ''];

        $sourceChats = array_values(array_filter(EnumTelegramEvents::DAILY_DIGEST_COLLECT->getIds()));

        if (empty($sourceChats)) {
            return $empty;
        }

        $rows = TelegramMessage::query()
            ->where('direction', EnumTelegramLoggerDirection::IN->value)
            ->whereIn('chat_id', $sourceChats)
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->whereNotNull('text')
            ->get(['raw']);

        $total = $rows->count();

        if ($total === 0) {
            return $empty;
        }

        $counts = [];
        foreach ($rows as $row) {
            $author = $this->author($row->raw);
            if ($author !== null) {
                $counts[$author] = ($counts[$author] ?? 0) + 1;
            }
        }

        arsort($counts);
        $top = array_slice($counts, 0, self::TOP_LIMIT, true);

        $lines = ["📊 Повідомлень за день: {$total}"];

        if (! empty($top)) {
            $topText = collect($top)
                ->map(fn (int $count, string $name): string => "{$name} ({$count})")
                ->implode(', ');
            $lines[] = "🔥 Топ активних: {$topText}";
        }

        return ['total' => $total, 'top' => $top, 'text' => implode("\n", $lines)];
    }

    private function author(mixed $raw): ?string
    {
        $username = data_get($raw, 'message.from.username');
        if ($username) {
            return '@'.$username;
        }

        $firstName = trim((string) data_get($raw, 'message.from.first_name'));

        return $firstName !== '' ? $firstName : null;
    }
}
