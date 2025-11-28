<?php

namespace App\Services\Gemini;

class GeminiRequestDto
{
    public function __construct(readonly array $json)
    {
    }

    public function getText(): string
    {
        return $this->json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
}
