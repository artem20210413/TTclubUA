<?php

namespace App\Services\Gemini;


use Illuminate\Support\Facades\Http;

enum GeminiModel: string
{
    case FLASH = 'gemini-2.5-flash';
    case PRO = 'gemini-2.5-pro';
    case FLASH_THINKING = 'gemini-2.0-flash-thinking';
}

class GeminiService
{
    public static function generate(string $prompt, GeminiModel $model = GeminiModel::FLASH): GeminiRequestDto
    {dd($prompt);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model->value}:generateContent";

        $response = Http::withHeaders([
            'x-goog-api-key' => config('services.gemini.key'),
        ])->post($url, [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ]);

        if (!$response->ok()) {
            throw new \Exception("Gemini API error: " . $response->body());
        }

        return new GeminiRequestDto($response->json());
    }
}
