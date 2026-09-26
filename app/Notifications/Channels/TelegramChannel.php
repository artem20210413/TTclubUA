<?php

namespace App\Notifications\Channels;

use App\Eloquent\TelegramLoggerEloquent;
use App\Enum\EnumTelegramEvents;
use App\Jobs\PinTelegramMessageJob;
use App\Notifications\Support\TelegramMessagePayload;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;

class TelegramChannel
{
    public function __construct(private readonly Api $telegram) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        $chatId = $notifiable->routeNotificationFor('telegram', $notification);

        if (empty($chatId)) {
            return;
        }

        if (! method_exists($notification, 'toTelegram')) {
            return;
        }

        /** @var TelegramMessagePayload $payload */
        $payload = $notification->toTelegram($notifiable);

        if (! $payload->text && ! $payload->photo && ! $payload->document && empty($payload->mediaGroup)) {
            return;
        }

        $params = $this->buildParams($chatId, $payload);

        try {
            if (! empty($payload->mediaGroup)) {
                $this->telegram->sendMediaGroup($params);
            } elseif ($payload->photo) {
                $this->telegram->sendPhoto($params);
            } elseif ($payload->document) {
                $this->telegram->sendDocument($params);
            } else {
                $response = $this->telegram->sendMessage($params);

                if ($payload->pin) {
                    PinTelegramMessageJob::dispatch($chatId, $response->message_id, $payload->pinUntil);
                }
            }

            TelegramLoggerEloquent::createOut($params);
        } catch (\Throwable $e) {
            Log::error('Telegram send error: '.$e->getMessage(), [
                'chat_id' => $chatId,
                'notification' => $notification::class,
            ]);

            TelegramLoggerEloquent::createOut($params);

            $this->alertSystemErrors($chatId, $notification::class, $e);
        }
    }

    /**
     * Best-effort alert to the system-errors chat for any failed Telegram send, of any
     * notification type. Never throws itself, and skips the error chat to avoid recursion.
     */
    private function alertSystemErrors(string|int $failedChatId, string $notificationClass, \Throwable $e): void
    {
        foreach (EnumTelegramEvents::SYSTEM_ERRORS->getIds() as $errorChatId) {
            if (empty($errorChatId) || (string) $errorChatId === (string) $failedChatId) {
                continue;
            }

            try {
                $this->telegram->sendMessage([
                    'chat_id' => $errorChatId,
                    'text' => "⚠️ Помилка відправки Telegram ({$notificationClass}) у чат {$failedChatId}: {$e->getMessage()}",
                ]);
            } catch (\Throwable $alertError) {
                Log::error('Failed to notify system-errors chat: '.$alertError->getMessage());
            }
        }
    }

    private function buildParams(string|int $chatId, TelegramMessagePayload $payload): array
    {
        if (! empty($payload->mediaGroup)) {
            $media = [];
            $params = ['chat_id' => $chatId];

            foreach ($payload->mediaGroup as $index => $path) {
                $attachmentName = 'photo'.$index;
                $mediaItem = ['type' => 'photo'];

                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    $mediaItem['media'] = $path;
                } else {
                    $mediaItem['media'] = 'attach://'.$attachmentName;
                    $params[$attachmentName] = InputFile::create($path);
                }

                if ($index === 0 && $payload->text) {
                    $mediaItem['caption'] = $payload->text;
                    $mediaItem['parse_mode'] = $payload->parseMode;
                }

                $media[] = $mediaItem;
            }

            $params['media'] = json_encode($media);

            return $params;
        }

        if ($payload->photo) {
            return [
                'chat_id' => $chatId,
                'photo' => fopen($payload->photo, 'r'),
                'caption' => $payload->text ?? '',
                'parse_mode' => $payload->parseMode,
            ];
        }

        if ($payload->document) {
            return [
                'chat_id' => $chatId,
                'document' => fopen($payload->document, 'r'),
                'caption' => $payload->text ?? '',
                'parse_mode' => $payload->parseMode,
            ];
        }

        $params = [
            'chat_id' => $chatId,
            'text' => $payload->text,
            'parse_mode' => $payload->parseMode,
            'disable_web_page_preview' => $payload->disableWebPagePreview,
        ];

        if ($payload->replyMarkup !== null) {
            $params['reply_markup'] = json_encode($payload->replyMarkup);
        } elseif (! empty($payload->buttons)) {
            $inlineKeyboard = [];
            foreach ($payload->buttons as $text => $url) {
                $inlineKeyboard[] = [['text' => $text, 'url' => $url]];
            }
            $params['reply_markup'] = json_encode(['inline_keyboard' => $inlineKeyboard]);
        }

        return $params;
    }
}
