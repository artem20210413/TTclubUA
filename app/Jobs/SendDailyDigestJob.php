<?php

namespace App\Jobs;

use App\Enum\EnumTelegramEvents;
use App\Models\DailyDigest;
use App\Notifications\AdHocMessageNotification;
use App\Notifications\Support\TelegramMessagePayload;
use App\Notifications\Support\TelegramRecipients;
use App\Services\Digest\BirthdayGreetingProvider;
use App\Services\Digest\Contracts\DigestSummarizer;
use App\Services\Digest\DailyDigestCollector;
use App\Services\Digest\DailyDigestComposer;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Generates and delivers the once-per-day digest to the main chat.
 * Idempotent per calendar day (unique daily_digests.digest_date) and retried
 * after 5/10/30 minutes; on exhausted retries falls back to greetings only (FR-008).
 */
class SendDailyDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    private CarbonImmutable $date;

    public function __construct(?string $date = null)
    {
        $this->date = $date ? CarbonImmutable::parse($date) : CarbonImmutable::today();
    }

    /**
     * Retry after 5, 10, 30 minutes (FR-008).
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [300, 600, 1800];
    }

    public function handle(
        DailyDigestCollector $collector,
        DigestSummarizer $summarizer,
        BirthdayGreetingProvider $greetings,
        DailyDigestComposer $composer,
    ): void {
        $digest = $this->claimDigest();

        if ($digest === null) {
            return; // already delivered/greetings_only for this day (idempotency)
        }

        $messages = $collector->forDate($this->date);
        $greetingText = $greetings->forDate($this->date);
        $birthdayCount = $greetings->membersForDate($this->date)->count();

        $summary = $summarizer->summarize($messages); // may throw → job retries

        $message = $composer->compose($summary, $greetingText);

        $this->send($message);

        $digest->update([
            'status' => DailyDigest::STATUS_DELIVERED,
            'message' => $message,
            'source_message_count' => count($messages),
            'birthday_user_count' => $birthdayCount,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Claim (or create) today's digest row. Returns null when it is already finalized.
     */
    private function claimDigest(): ?DailyDigest
    {
        $digest = DailyDigest::firstOrCreate(
            ['digest_date' => $this->date->toDateString()],
            ['status' => DailyDigest::STATUS_PENDING],
        );

        return $digest->isFinalized() ? null : $digest;
    }

    private function send(string $message): void
    {
        Notification::send(
            TelegramRecipients::routes(EnumTelegramEvents::DAILY_DIGEST->getIds()),
            new AdHocMessageNotification(new TelegramMessagePayload(text: $message)),
        );
    }

    /**
     * After all retries are exhausted: send birthday greetings only when any exist,
     * otherwise mark the day failed. Never double-post (FR-008).
     */
    public function failed(\Throwable $e): void
    {
        Log::error('SendDailyDigestJob failed: '.$e->getMessage(), ['date' => $this->date->toDateString()]);

        $digest = DailyDigest::firstOrCreate(
            ['digest_date' => $this->date->toDateString()],
            ['status' => DailyDigest::STATUS_PENDING],
        );

        if ($digest->isFinalized()) {
            return;
        }

        $greetings = app(BirthdayGreetingProvider::class);
        $members = $greetings->membersForDate($this->date);

        if ($members->isEmpty()) {
            $digest->update(['status' => DailyDigest::STATUS_FAILED]);

            return;
        }

        $message = $greetings->forDate($this->date); // greetings only, no summary block

        try {
            $this->send($message);
            $digest->update([
                'status' => DailyDigest::STATUS_GREETINGS_ONLY,
                'message' => $message,
                'birthday_user_count' => $members->count(),
                'delivered_at' => now(),
            ]);
        } catch (\Throwable $sendError) {
            Log::error('SendDailyDigestJob greetings-only fallback failed: '.$sendError->getMessage());
            $digest->update(['status' => DailyDigest::STATUS_FAILED]);
        }
    }
}
