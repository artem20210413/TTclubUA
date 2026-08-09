<?php

use App\Services\Digest\DailyDigestComposer;

it('includes the summary when present', function () {
    $message = (new DailyDigestComposer)->compose('🔧 Хтось продає ракетку');

    expect($message)->toContain('🔧 Хтось продає ракетку')
        ->and($message)->toContain('Підсумок дня');
});

it('shows a no-highlights line when there is no summary', function () {
    $message = (new DailyDigestComposer)->compose(null);

    expect($message)->toContain('Сьогодні без помітних обговорень.');
});

it('appends the greeting after the summary', function () {
    $message = (new DailyDigestComposer)->compose('Підсумок', '🎉 Вітаємо Іван!');

    expect($message)->toContain('Підсумок')
        ->and($message)->toContain('🎉 Вітаємо Іван!');
});
