<?php

namespace App\Services\Telegram;

use App\Models\User;
use App\Services\Telegram\Dto\TelegramCallbackQueryDto;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

/**
 * Обробляє натискання кнопок у повідомленні про вхід: «Це не я» → «Так / Ні» → завершення всіх сесій.
 *
 * Виняток із правила «надсилати лише через TelegramChannel»: відповідь на натискання
 * (answerCallbackQuery) і редагування натиснутого повідомлення (editMessage*) — це відповіді на
 * вхідне оновлення в тому самому чаті, а TelegramChannel уміє лише надсилати нові повідомлення.
 * Тому тут Telegram\Bot\Api викликається напряму, і лише тут.
 */
class TelegramCallbackHandler
{
    public const ASK = 'sess:ask';

    public const YES = 'sess:yes';

    public const NO = 'sess:no';

    public function __construct(private readonly Api $telegram) {}

    /** Кнопка «Це не я — завершити всі сесії» для повідомлення про вхід. */
    public static function askKeyboard(): array
    {
        return ['inline_keyboard' => [[
            ['text' => __('telegram.sessions_revoke.button_ask'), 'callback_data' => self::ASK],
        ]]];
    }

    /**
     * Ніколи не кидає винятків: помилки логуються, а на натискання завжди відповідаємо,
     * щоб кнопка в клієнті Telegram не «крутилася».
     */
    public function handle(TelegramCallbackQueryDto $callbackQuery): void
    {
        $toast = null;

        try {
            $toast = $this->process($callbackQuery);
        } catch (\Throwable $e) {
            Log::error('Telegram callback: '.$e->getMessage(), [
                'data' => $callbackQuery->getData(),
                'from_id' => $callbackQuery->getFromId(),
            ]);
            $toast = __('telegram.sessions_revoke.failed');
        } finally {
            $this->answer($callbackQuery, $toast);
        }
    }

    /**
     * @return string|null текст короткої підказки для answerCallbackQuery
     */
    private function process(TelegramCallbackQueryDto $callbackQuery): ?string
    {
        $data = $callbackQuery->getData();
        $fromId = $callbackQuery->getFromId();

        if (! in_array($data, [self::ASK, self::YES, self::NO], true) || $fromId === null) {
            return null;
        }

        // Члена клубу визначаємо лише за тим, хто натиснув (from.id задає Telegram).
        // Дані кнопки нікого не ідентифікують, тож переслане повідомлення чужому нічого не дає.
        $users = User::query()->where('telegram_id', $fromId)->get();

        if ($users->isEmpty()) {
            return null;
        }

        return match ($data) {
            self::ASK => $this->replaceKeyboard($callbackQuery, $this->confirmKeyboard(), 'telegram.sessions_revoke.confirm'),
            self::NO => $this->replaceKeyboard($callbackQuery, self::askKeyboard(), 'telegram.sessions_revoke.cancelled'),
            self::YES => $this->revokeSessions($callbackQuery, $users),
        };
    }

    private function replaceKeyboard(TelegramCallbackQueryDto $callbackQuery, array $keyboard, string $toastKey): string
    {
        $this->telegram->editMessageReplyMarkup([
            'chat_id' => $callbackQuery->getChatId(),
            'message_id' => $callbackQuery->getMessageId(),
            'reply_markup' => json_encode($keyboard),
        ]);

        return __($toastKey);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    private function revokeSessions(TelegramCallbackQueryDto $callbackQuery, $users): ?string
    {
        // Спершу завершуємо сесії, потім редагуємо повідомлення: помилка Telegram не має їх скасувати.
        $count = 0;
        foreach ($users as $user) {
            $count += $user->tokens()->delete();
            $user->clearCode();
        }

        Log::warning('Користувач завершив усі сесії кнопкою «Це не я»', [
            'user_ids' => $users->pluck('id')->all(),
            'telegram_id' => $callbackQuery->getFromId(),
            'tokens_deleted' => $count,
        ]);

        $doneText = TelegramBotHelpers::renderTemplate('sessions_revoke.done', ['{count}' => $count]);

        try {
            // Без reply_markup Telegram прибирає кнопки з повідомлення.
            $this->telegram->editMessageText([
                'chat_id' => $callbackQuery->getChatId(),
                'message_id' => $callbackQuery->getMessageId(),
                'text' => $doneText,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Throwable $e) {
            // Сесії вже завершено — показуємо результат хоча б підказкою.
            Log::error('Telegram editMessageText після завершення сесій: '.$e->getMessage());

            return $doneText;
        }

        return null;
    }

    private function confirmKeyboard(): array
    {
        return ['inline_keyboard' => [[
            ['text' => __('telegram.sessions_revoke.button_yes'), 'callback_data' => self::YES],
            ['text' => __('telegram.sessions_revoke.button_no'), 'callback_data' => self::NO],
        ]]];
    }

    private function answer(TelegramCallbackQueryDto $callbackQuery, ?string $toast): void
    {
        try {
            $this->telegram->answerCallbackQuery(
                ['callback_query_id' => $callbackQuery->getId()] + ($toast ? ['text' => $toast] : [])
            );
        } catch (\Throwable $e) {
            Log::error('Telegram answerCallbackQuery: '.$e->getMessage());
        }
    }
}
