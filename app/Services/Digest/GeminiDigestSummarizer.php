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
        return trim(str_replace('**', '', $text));
    }
}
