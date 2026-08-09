<?php

use App\Services\Digest\DailyDigestComposer;

it('includes the summary when present', function () {
    $message = (new DailyDigestComposer)->compose('🔧 Хтось продає ракетку');

    expect($message)->toContain('🔧 Хтось продає ракетку')
        ->and($message)->toContain('Підсумок дня');
});

it('shows a no-highlights line when there is no summary', function () {
    $message = (new DailyDigestComposer)->compose(null);

    expect($message)->toContain('Сьогодні в гаражі тихо, без рухів.');
});

it('appends the greeting after the summary', function () {
    $message = (new DailyDigestComposer)->compose('Підсумок', '🎉 Вітаємо Іван!');

    expect($message)->toContain('Підсумок')
        ->and($message)->toContain('🎉 Вітаємо Іван!');
});

it('caps the message to the Telegram limit while keeping the greeting intact', function () {
    $greeting = '🎉 Вітаємо Іван!';
    $message = (new DailyDigestComposer)->compose(str_repeat('дуже довгий підсумок ', 500), $greeting);

    expect(mb_strlen($message))->toBeLessThanOrEqual(4096)
        ->and($message)->toEndWith($greeting);
});
