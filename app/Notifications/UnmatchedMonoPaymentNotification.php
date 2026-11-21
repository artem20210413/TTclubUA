<?php

namespace App\Notifications;

use App\Models\MonoTransaction;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class UnmatchedMonoPaymentNotification extends Notification
{
    /**
     * @param  MonoTransaction|null  $usedLink  the earlier transaction that owns the payment code, if the code was already used
     */
    public function __construct(
        private readonly MonoTransaction $payment,
        private readonly bool $hasCode,
        private readonly ?MonoTransaction $usedLink = null,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $text = TelegramBotHelpers::renderTemplate('mono_unmatched_payment.message', [
            '{amount}' => number_format(($this->payment->amount ?? 0) / 100, 2, '.', ' '),
            // Monobank prefixes the payer name with "Від: ", the template already labels it.
            '{sender}' => $this->escape(preg_replace('/^Від:\s*/u', '', (string) $this->payment->description)),
            '{comment}' => $this->escape($this->payment->comment),
            '{time}' => $this->payment->created_at?->format('d.m.Y H:i') ?? '-',
            '{reason}' => $this->reason(),
            '{id}' => $this->payment->id,
        ]);

        return new TelegramMessagePayload(text: $text);
    }

    private function reason(): string
    {
        if (! $this->hasCode) {
            return __('telegram.mono_unmatched_payment.reason_no_code');
        }

        if ($this->usedLink) {
            return TelegramBotHelpers::renderTemplate('mono_unmatched_payment.reason_used_link', [
                '{owner}' => TelegramBotHelpers::TryMentionPerson($this->usedLink->user),
                '{owner_id}' => $this->usedLink->user_id ?? '-',
            ]);
        }

        return __('telegram.mono_unmatched_payment.reason_unknown_code');
    }

    // Sender name and comment are typed by the payer, so they must not break Telegram's HTML parser.
    private function escape(?string $value): string
    {
        return $value === null || $value === '' ? '-' : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
