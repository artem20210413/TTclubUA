<?php

use App\Jobs\SendDailyDigestJob;
use App\Models\DailyDigest;
use App\Models\TelegramMessage;
use App\Models\User;
use App\Notifications\AdHocMessageNotification;
use App\Services\Digest\Contracts\DigestSummarizer;
use Illuminate\Support\Facades\Notification;

const MAIN_CHAT = '111222';

beforeEach(function () {
    config()->set('telegram.chats.tt_club_ua', MAIN_CHAT);   // DAILY_DIGEST_COLLECT source
    config()->set('telegram.chats.test_bot_2', MAIN_CHAT);   // DAILY_DIGEST delivery
    Notification::fake();
});

function fakeSummarizer(string $return = 'Підсумок', ?Throwable $throw = null): void
{
    app()->bind(DigestSummarizer::class, fn () => new class($return, $throw) implements DigestSummarizer
    {
        public function __construct(private string $return, private ?Throwable $throw) {}

        public function summarize(array $messages): string
        {
            if ($this->throw) {
                throw $this->throw;
            }

            return $this->return;
        }
    });
}

function seedIncoming(string $text, string $chat = MAIN_CHAT): void
{
    TelegramMessage::create([
        'chat_id' => $chat,
        'direction' => 'in',
        'text' => $text,
        'message_id' => (string) random_int(1, 999999),
    ]);
}

function sentOnDemandCount(): int
{
    $count = 0;

    try {
        Notification::assertSentOnDemand(AdHocMessageNotification::class, function () use (&$count) {
            $count++;

            return true;
        });
    } catch (\PHPUnit\Framework\AssertionFailedError) {
        // assertSentOnDemand() itself asserts "at least one sent"; nothing sent is a valid 0.
    }

    return $count;
}

it('posts one digest to the main chat and marks it delivered (US1)', function () {
    fakeSummarizer('🔧 Хтось продає ракетку');
    seedIncoming('Продам ракетку');
    seedIncoming('Хто йде на тренування?');

    SendDailyDigestJob::dispatchSync();

    $digest = DailyDigest::whereDate('digest_date', today())->firstOrFail();
    expect($digest->status)->toBe(DailyDigest::STATUS_DELIVERED)
        ->and($digest->source_message_count)->toBe(2)
        ->and(sentOnDemandCount())->toBe(1);
});

it('does not post a second digest for the same day (idempotency, FR-007)', function () {
    fakeSummarizer();
    seedIncoming('Привіт');

    SendDailyDigestJob::dispatchSync();
    SendDailyDigestJob::dispatchSync();

    expect(DailyDigest::whereDate('digest_date', today())->count())->toBe(1)
        ->and(sentOnDemandCount())->toBe(1);
});

it('marks the day failed and posts nothing when the AI fails and there are no birthdays (FR-008)', function () {
    seedIncoming('Привіт');
    $job = new SendDailyDigestJob;

    $job->failed(new RuntimeException('Gemini down'));

    $digest = DailyDigest::whereDate('digest_date', today())->firstOrFail();
    expect($digest->status)->toBe(DailyDigest::STATUS_FAILED)
        ->and(sentOnDemandCount())->toBe(0);
});

it('greets today active main-chat members who have a telegram_id (US2)', function () {
    fakeSummarizer('Підсумок');
    User::factory()->create([
        'name' => 'Іван',
        'telegram_id' => 777,
        'birth_date' => now()->subYears(30)->format('Y-m-d'),
        'active' => 1,
    ]);

    SendDailyDigestJob::dispatchSync();

    $digest = DailyDigest::whereDate('digest_date', today())->firstOrFail();
    expect($digest->birthday_user_count)->toBe(1)
        ->and($digest->message)->toContain('Іван');
});

it('excludes birthday users without telegram_id or inactive (FR-004)', function () {
    fakeSummarizer('Підсумок');
    User::factory()->create([
        'name' => 'NoTg',
        'telegram_id' => null,
        'birth_date' => now()->subYears(25)->format('Y-m-d'),
        'active' => 1,
    ]);
    User::factory()->create([
        'name' => 'Inactive',
        'telegram_id' => 888,
        'birth_date' => now()->subYears(25)->format('Y-m-d'),
        'active' => 0,
    ]);

    SendDailyDigestJob::dispatchSync();

    $digest = DailyDigest::whereDate('digest_date', today())->firstOrFail();
    expect($digest->birthday_user_count)->toBe(0)
        ->and($digest->message)->not->toContain('NoTg')
        ->and($digest->message)->not->toContain('Inactive');
});

it('falls back to greetings only when AI fails but birthdays exist (FR-008, greetings_only)', function () {
    User::factory()->create([
        'name' => 'Оля',
        'telegram_id' => 999,
        'birth_date' => now()->subYears(22)->format('Y-m-d'),
        'active' => 1,
    ]);

    (new SendDailyDigestJob)->failed(new RuntimeException('Gemini down'));

    $digest = DailyDigest::whereDate('digest_date', today())->firstOrFail();
    expect($digest->status)->toBe(DailyDigest::STATUS_GREETINGS_ONLY)
        ->and($digest->message)->toContain('Оля')
        ->and(sentOnDemandCount())->toBe(1);
});
