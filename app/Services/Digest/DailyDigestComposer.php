<?php

namespace App\Services\Digest;

/**
 * Combines the AI summary and birthday greetings into the final Ukrainian message.
 */
class DailyDigestComposer
{
    private const HEADER = "📋 <b>Підсумок дня</b>\n\n";

    private const NO_HIGHLIGHTS = 'Сьогодні в гаражі тихо, без рухів.';

    /** Telegram sendMessage hard limit. */
    private const TELEGRAM_MAX = 4096;

    public function compose(?string $summary, ?string $stats = null, ?string $greeting = null): string
    {
        $summary = trim((string) $summary);
        $stats = trim((string) $stats);
        $greeting = trim((string) $greeting);

        $body = $summary !== '' ? $summary : self::NO_HIGHLIGHTS;

        // Header + these blocks must stay intact; only the AI summary body is trimmed to fit the limit.
        $tail = '';
        foreach ([$stats, $greeting] as $block) {
            if ($block !== '') {
                $tail .= "\n\n".$block;
            }
        }

        $body = $this->trim($body, self::TELEGRAM_MAX - mb_strlen(self::HEADER) - mb_strlen($tail));

        return self::HEADER.$body.$tail;
    }

    private function trim(string $text, int $max): string
    {
        if ($max <= 0) {
            return '';
        }

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $max - 1)).'…';
    }
}
