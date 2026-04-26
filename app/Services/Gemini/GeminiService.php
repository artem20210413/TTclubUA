<?php

namespace App\Services\Gemini;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

enum GeminiModel: string
{
    case FLASH = 'gemini-2.5-flash';
    case PRO = 'gemini-2.5-pro';
    case FLASH_THINKING = 'gemini-2.0-flash-thinking';
}

class GeminiService
{
    /** dd(GeminiService::generate(Prompt::buildBirthdayPrompt(User::find(1)))->getText()); */
    public static function generate(string $prompt, GeminiModel $model = GeminiModel::FLASH): GeminiRequestDto
    {
        Log::info("Gemini AI: Sending request to {$model->value}", [
//            'prompt_preview' => mb_substr($prompt, 0, 500) . '...', // щоб не забивати лог гігантським текстом
            'prompt_preview' => $prompt // щоб не забивати лог гігантським текстом
        ]);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model->value}:generateContent";
        $startTime = microtime(true);
        $response = Http::withHeaders([
            'x-goog-api-key' => config('services.gemini.key'),
        ])->timeout(420)
            // Також можна додати connectTimeout, щоб швидше розуміти, чи живий сервер
            ->connectTimeout(10)
            ->post($url, [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt]
                        ]
                    ]
                ]
            ]);

        $duration = round(microtime(true) - $startTime, 2);

        if (!$response->ok()) {
            throw new \Exception("Gemini API error: " . $response->body());
        }

        $dto = new GeminiRequestDto($response->json());
        Log::info("Gemini AI: Response received", [
            'duration' => $duration . 's',
            'response_preview' => $dto->getText()
        ]);

        return $dto;
    }
}
