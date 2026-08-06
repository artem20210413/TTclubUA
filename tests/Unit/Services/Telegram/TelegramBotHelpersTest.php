<?php

use App\Services\Telegram\TelegramBotHelpers;
use Tests\TestCase;

uses(TestCase::class);

test('renderTemplate substitutes tokens for an existing translation key', function () {
    $text = TelegramBotHelpers::renderTemplate('commands.default');

    expect($text)->toBe('🤖 Невідома команда. Введіть /help, щоб переглянути список доступних команд.');
});

test('renderTemplate substitutes {token} placeholders', function () {
    $text = TelegramBotHelpers::renderTemplate('commands.start', ['{name}' => 'Іван']);

    expect($text)->toContain('Іван');
});

test('renderTemplate falls back to the default when the key is missing', function () {
    $text = TelegramBotHelpers::renderTemplate('commands.does_not_exist', [], 'fallback text');

    expect($text)->toBe('fallback text');
});
