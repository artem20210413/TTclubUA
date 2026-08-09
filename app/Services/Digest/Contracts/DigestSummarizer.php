<?php

namespace App\Services\Digest\Contracts;

interface DigestSummarizer
{
    /**
     * Summarize a day's chat messages into a concise Ukrainian digest of useful
     * highlights (buy/sell, arrangements, notable Q&A). Must not fabricate content
     * that is not present in the input.
     *
     * @param  array<int, string>  $messages  Trimmed, deduped chat message texts for the day.
     * @return string Concise Ukrainian summary.
     *
     * @throws \Throwable When the AI backend is unavailable (the job will retry).
     */
    public function summarize(array $messages): string;
}
