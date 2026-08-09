<?php

namespace App\Services\Digest;

/**
 * Combines the AI summary and birthday greetings into the final Ukrainian message.
 */
class DailyDigestComposer
{
    private const NO_HIGHLIGHTS = 'Сьогодні в гаражі тихо, без рухів.';

    /** Telegram sendMessage hard limit. */
    private const TELEGRAM_MAX = 4096;

    public function compose(?string $summary, ?string $greeting = null): string
    {
        $summary = trim((string) $summary);
        $greeting = trim((string) $greeting);

        $header = "📋 <b>Підсумок дня</b>\n";
        $body = $summary !== '' ? $summary : self::NO_HIGHLIGHTS;

        // Keep the birthday greeting intact; trim only the summary body to fit Telegram's limit.
        if ($greeting !== '') {
            $reserved = mb_strlen($header) + mb_strlen("\n\n".$greeting);
            $body = $this->trim($body, self::TELEGRAM_MAX - $reserved);

            return $header.$body."\n\n".$greeting;
        }

        return $this->trim($header.$body, self::TELEGRAM_MAX);
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
