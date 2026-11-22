<?php

use Telegram\Bot\Api;
use Telegram\Bot\Objects\WebhookInfo;

const CURRENT_WEBHOOK_URL = 'https://www.ttclub.com.ua/api/telegram/webhook';

beforeEach(function () {
    config(['services.telegram.webhook_secret' => 'test-secret']);
    $this->api = Mockery::mock(Api::class);
    $this->app->instance(Api::class, $this->api);
});

function webhookInfo(array $data): WebhookInfo
{
    return new WebhookInfo($data);
}

test('it re-registers the current URL with the secret and allowed updates including callback_query', function () {
    $this->api->shouldReceive('getWebhookInfo')->andReturn(
        webhookInfo(['url' => CURRENT_WEBHOOK_URL, 'allowed_updates' => ['message', 'chat_member', 'my_chat_member']]),
    );
    $this->api->shouldReceive('setWebhook')->once()->with([
        'url' => CURRENT_WEBHOOK_URL,
        'secret_token' => 'test-secret',
        'allowed_updates' => ['message', 'chat_member', 'my_chat_member', 'callback_query'],
    ])->andReturn(true);

    $this->artisan('tg:set-webhook')
        ->expectsConfirmation('Зареєструвати вебхук із цими параметрами?', 'yes')
        ->assertSuccessful();
});

test('without a current webhook it uses APP_URL', function () {
    config(['app.url' => 'https://example.test/']);
    $this->api->shouldReceive('getWebhookInfo')->andReturn(webhookInfo(['url' => '']));
    $this->api->shouldReceive('setWebhook')->once()
        ->withArgs(fn (array $params) => $params['url'] === 'https://example.test/api/telegram/webhook')
        ->andReturn(true);

    $this->artisan('tg:set-webhook')
        ->expectsConfirmation('Зареєструвати вебхук із цими параметрами?', 'yes')
        ->assertSuccessful();
});

test('it refuses to register without a webhook secret', function () {
    config(['services.telegram.webhook_secret' => null]);
    $this->api->shouldReceive('getWebhookInfo')->andReturn(webhookInfo(['url' => CURRENT_WEBHOOK_URL]));
    $this->api->shouldNotReceive('setWebhook');

    $this->artisan('tg:set-webhook')->assertFailed();
});

test('--info only shows the current settings', function () {
    $this->api->shouldReceive('getWebhookInfo')->once()->andReturn(webhookInfo(['url' => CURRENT_WEBHOOK_URL]));
    $this->api->shouldNotReceive('setWebhook');

    $this->artisan('tg:set-webhook --info')->assertSuccessful();
});

test('declining the confirmation leaves the webhook unchanged', function () {
    $this->api->shouldReceive('getWebhookInfo')->andReturn(webhookInfo(['url' => CURRENT_WEBHOOK_URL]));
    $this->api->shouldNotReceive('setWebhook');

    $this->artisan('tg:set-webhook')
        ->expectsConfirmation('Зареєструвати вебхук із цими параметрами?', 'no')
        ->assertSuccessful();
});

test('it warns about update types that would stop arriving', function () {
    $this->api->shouldReceive('getWebhookInfo')->andReturn(
        webhookInfo(['url' => CURRENT_WEBHOOK_URL, 'allowed_updates' => ['message', 'edited_message']]),
    );
    $this->api->shouldNotReceive('setWebhook');

    $this->artisan('tg:set-webhook')
        ->expectsOutputToContain('Перестануть приходити: edited_message')
        ->expectsConfirmation('Зареєструвати вебхук із цими параметрами?', 'no')
        ->assertSuccessful();
});
