<?php

namespace App\Services\Digest;

use App\Services\Digest\Contracts\DigestSummarizer;
use App\Services\Gemini\GeminiModel;
use App\Services\Gemini\GeminiService;
use App\Services\Gemini\Prompt\Prompt;

/**
 * Summarizes the day's messages via Gemini (cheapest FLASH model) using a
 * token-frugal prompt. Exceptions propagate so the job can retry.
 */
class GeminiDigestSummarizer implements DigestSummarizer
{
    public function summarize(array $messages): string
    {
        if ($messages === []) {
            return ''; // no messages at all → composer shows the calm fallback line, no AI call
        }

        $prompt = Prompt::buildDailyDigestPrompt($messages);

        $text = GeminiService::generate($prompt, GeminiModel::FLASH)->getText();

        // Strip markdown bold that Telegram HTML mode would render as literal '**'.
        $text = str_replace('**', '', $text);

        return trim($this->linkify($text));
    }

    /**
     * Per bullet: hide raw t.me URLs behind a single "детальніше" HTML link.
     * Collapses multiple links on one line to the first (most relevant) one.
     */
    private function linkify(string $text): string
    {
        $url = 'https?:\/\/t\.me\/c\/\d+\/\d+';

        return collect(explode("\n", $text))
            ->map(function (string $line) use ($url): string {
                $line = str_replace('🔗', '', $line);

                if (! preg_match('#'.$url.'#u', $line, $m)) {
                    return $line;
                }

                $first = $m[0];
                // Remove every link (and any leading comma/space) from the line…
                $line = preg_replace('#[\s,]*'.$url.'#u', '', $line);

                // …then append a single hidden link.
                return rtrim($line, " \t,;").' <a href="'.$first.'">детальніше</a>';
            })
            ->implode("\n");
    }
}
