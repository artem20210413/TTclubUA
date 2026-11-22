<?php

use App\Jobs\Autoria\SearchCarsJob;
use App\Jobs\Autoria\SyncCarDetailJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Telegram\Bot\Api;

beforeEach(function () {
    config([
        'telegram.chats.system_errors' => 'errors-chat',
        'services.autoria.api_key' => 'test-key',
    ]);

    $this->telegram = Mockery::mock(Api::class);
    app()->instance(Api::class, $this->telegram);
});

function fakeAutoria(int $status, string $body = '{"error":"test"}'): void
{
    Http::fake(['developers.ria.com/*' => Http::response($body, $status)]);
}

function isAutoriaPackageAlert(array $params): bool
{
    $button = json_decode($params['reply_markup'] ?? '', true)['inline_keyboard'][0][0] ?? [];

    return $params['chat_id'] === 'errors-chat'
        && str_contains($params['text'], 'AUTO.RIA: синхронізацію авто зупинено')
        && ($button['text'] ?? null) === '🔄 Продовжити пакет'
        && ($button['url'] ?? null) === 'https://developers.ria.com/payment/';
}

function runAutoriaJob(SearchCarsJob|SyncCarDetailJob $job): SearchCarsJob|SyncCarDetailJob
{
    $job->withFakeQueueInteractions();
    $job->handle();

    return $job;
}

// US1 — admins are told when the AUTO.RIA package runs out

it('alerts the system-errors chat with a renew button when AUTO.RIA rejects the search with 403', function () {
    fakeAutoria(403);
    $this->telegram->shouldReceive('sendMessage')->once()->withArgs(fn (array $params) => isAutoriaPackageAlert($params))->andReturn([]);

    runAutoriaJob(new SearchCarsJob);
});

it('sends only one alert a day however many jobs get 403', function () {
    fakeAutoria(403);
    $this->telegram->shouldReceive('sendMessage')->once()->andReturn([]);

    runAutoriaJob(new SearchCarsJob);
    runAutoriaJob(new SyncCarDetailJob(101));
    runAutoriaJob(new SyncCarDetailJob(102));
});

it('alerts again on the next day while the package is still exhausted', function () {
    fakeAutoria(403);
    $this->telegram->shouldReceive('sendMessage')->twice()->andReturn([]);

    runAutoriaJob(new SearchCarsJob);
    runAutoriaJob(new SearchCarsJob);

    $this->travel(1)->days();

    runAutoriaJob(new SearchCarsJob);
});

it('does not alert on the hourly limit (429)', function () {
    fakeAutoria(429);
    $this->telegram->shouldNotReceive('sendMessage');

    runAutoriaJob(new SearchCarsJob)->assertReleased();
    runAutoriaJob(new SyncCarDetailJob(101))->assertReleased();
});

it('still fails the job when the alert cannot be delivered', function () {
    fakeAutoria(403);
    $this->telegram->shouldReceive('sendMessage')->once()->andThrow(new RuntimeException('telegram is down'));

    runAutoriaJob(new SearchCarsJob)->assertFailed();
});

// US2 — the sync stops retrying once access is denied

it('fails the search after one attempt on 403 and starts no detail jobs', function () {
    Queue::fake();
    fakeAutoria(403);
    $this->telegram->shouldReceive('sendMessage')->once()->andReturn([]);

    $job = runAutoriaJob(new SearchCarsJob);

    $job->assertFailed();
    $job->assertNotReleased();
    Queue::assertNothingPushed();
});

it('fails the listing-detail job after one attempt on 403', function () {
    fakeAutoria(403);
    $this->telegram->shouldReceive('sendMessage')->once()->andReturn([]);

    $job = runAutoriaJob(new SyncCarDetailJob(101));

    $job->assertFailed();
    $job->assertNotReleased();
});

it('keeps the hourly limit as a release, not a failure', function () {
    fakeAutoria(429);
    $this->telegram->shouldNotReceive('sendMessage');

    $job = runAutoriaJob(new SearchCarsJob);

    $job->assertReleased();
    $job->assertNotFailed();
});

it('keeps throwing on other AUTO.RIA errors so the queue retries them, without alerting', function () {
    fakeAutoria(500);
    $this->telegram->shouldNotReceive('sendMessage');

    expect(fn () => runAutoriaJob(new SearchCarsJob))->toThrow(Exception::class, 'Search API невдало: 500')
        ->and(fn () => runAutoriaJob(new SyncCarDetailJob(101)))->toThrow(Exception::class, 'Detail API failed for ID 101');
});

// US3 — maintainers see AUTO.RIA's exact error text

it('logs the AUTO.RIA response on 403', function () {
    Log::spy();
    fakeAutoria(403, '{"error":{"code":"OVER_LIMIT"}}');
    $this->telegram->shouldReceive('sendMessage')->once()->andReturn([]);

    runAutoriaJob(new SearchCarsJob);

    Log::shouldHaveReceived('log')
        ->withArgs(fn (string $level, string $message, array $context = []) => $level === 'error'
            && str_contains($message, 'пошук, сторінка 0')
            && str_contains($message, 'HTTP 403')
            && ($context['body'] ?? null) === '{"error":{"code":"OVER_LIMIT"}}')
        ->once();
});

it('logs the AUTO.RIA response on 429', function () {
    Log::spy();
    fakeAutoria(429, 'Too Many Requests');
    $this->telegram->shouldNotReceive('sendMessage');

    runAutoriaJob(new SyncCarDetailJob(555));

    Log::shouldHaveReceived('log')
        ->withArgs(fn (string $level, string $message, array $context = []) => $level === 'warning'
            && str_contains($message, 'оголошення 555')
            && str_contains($message, 'HTTP 429')
            && ($context['body'] ?? null) === 'Too Many Requests')
        ->once();
});

it('keeps only an excerpt of a long AUTO.RIA response in the log', function () {
    Log::spy();
    fakeAutoria(429, str_repeat('x', 5000));
    $this->telegram->shouldNotReceive('sendMessage');

    runAutoriaJob(new SearchCarsJob);

    Log::shouldHaveReceived('log')
        ->withArgs(fn (string $level, string $message, array $context = []) => ($context['body'] ?? null) === str_repeat('x', 1000).'...')
        ->once();
});
