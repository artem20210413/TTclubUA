<?php

use App\Services\Digest\DailyDigestComposer;

it('includes the header and the summary when present', function () {
    $message = (new DailyDigestComposer)->compose('🔧 Хтось продає ракетку');

    expect($message)->toStartWith('📋 <b>Підсумок дня</b>')
        ->and($message)->toContain('🔧 Хтось продає ракетку');
});

it('shows a no-highlights line when there is no summary', function () {
    $message = (new DailyDigestComposer)->compose(null);

    expect($message)->toContain('Сьогодні в гаражі тихо, без рухів.');
});

it('appends stats and greeting after the summary', function () {
    $message = (new DailyDigestComposer)->compose('Підсумок', '📊 Повідомлень за день: 10', '🎉 Вітаємо Іван!');

    expect($message)->toContain('Підсумок')
        ->and($message)->toContain('📊 Повідомлень за день: 10')
        ->and($message)->toEndWith('🎉 Вітаємо Іван!');
});

it('caps the message to the Telegram limit while keeping stats and greeting intact', function () {
    $stats = '📊 Повідомлень за день: 10';
    $greeting = '🎉 Вітаємо Іван!';
    $message = (new DailyDigestComposer)->compose(str_repeat('дуже довгий підсумок ', 500), $stats, $greeting);

    expect(mb_strlen($message))->toBeLessThanOrEqual(4096)
        ->and($message)->toContain($stats)
        ->and($message)->toEndWith($greeting);
});
