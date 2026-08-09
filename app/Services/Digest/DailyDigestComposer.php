<?php

namespace App\Services\Digest;

/**
 * Combines the AI summary and birthday greetings into the final Ukrainian message.
 */
class DailyDigestComposer
{
    private const NO_HIGHLIGHTS = 'Сьогодні без помітних обговорень.';

    public function compose(?string $summary, ?string $greeting = null): string
    {
        $summary = trim((string) $summary);
        $greeting = trim((string) $greeting);

        $parts = [];

        $parts[] = "📋 <b>Підсумок дня</b>\n".($summary !== '' ? $summary : self::NO_HIGHLIGHTS);

        if ($greeting !== '') {
            $parts[] = $greeting;
        }

        return implode("\n\n", $parts);
    }
}
