<?php

use App\Services\Digest\GeminiDigestSummarizer;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

it('hides raw links behind a single "детальніше" per line and strips markdown', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => "🔧 **Тема:** щось сталося 🔗https://t.me/c/123/1, https://t.me/c/123/2\n⚡️ Інший пункт без лінка",
                ]]],
            ]],
        ]),
    ]);

    $out = (new GeminiDigestSummarizer)->summarize(['@a: привіт']);

    expect($out)->toContain('<a href="https://t.me/c/123/1">детальніше</a>')
        ->and($out)->not->toContain('https://t.me/c/123/2') // extra link collapsed
        ->and($out)->not->toContain('🔗')
        ->and($out)->not->toContain('**')
        ->and(substr_count($out, '<a href'))->toBe(1);
});

it('returns empty string without calling AI when there are no messages', function () {
    Http::fake(); // any call would record; we assert none happen

    $out = (new GeminiDigestSummarizer)->summarize([]);

    expect($out)->toBe('');
    Http::assertNothingSent();
});
