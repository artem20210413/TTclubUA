<?php

namespace App\Enum;

use Illuminate\Database\Eloquent\Collection;

enum EnumTelegramEvents
{
    //    case NOTIFICATION;
    case FA_FA;
    case STATS_MENTION;
    case CHANGE_USER;
    case LIST_BIRTHDAYS; // Список ДР в очереди
    case TEST;
    case SYSTEM_ERRORS;
    case MY;
    case USERS; // определенным пользователям
    case CUSTOM; // произвольный
    case EXPORT_USERS; // експорт всех пользователей
    case REGISTRATION; // при регистрации уведомление
    case SUGGESTION;
    case PURCHASE_REQUEST; // заявки на покупку товарів
    case DAILY_DIGEST; // куда отправляем ежедневный AI-дайджест
    case DAILY_DIGEST_COLLECT; // из каких чатов собираем сообщения для дайджеста
    case MEMBERSHIP; // чаты, где вступ/выход влияет на активность акаунта (только главная группа)
    case NEW_MEMBER_WELCOME; // белый список чатов, где бот приветствует новых участников
    case MONO_UNMATCHED_PAYMENT; // платіж у банку Monobank, який не вдалося прив'язати до учасника

    /**
     * Получить разрешение для качества
     */
    public function getIds(?Collection $users = null, array $chantIds = [], array $myIds = []): array
    {
        $isLocal = config('app.env') === 'local';

        $config = config('telegram.chats');

        $welcome = $config['welcome'] ?? '';
        $ttChat = $config['tt_club_ua'] ?? '';
        $testBot2 = $config['test_bot_2'] ?? '';
        $suggestions = $config['suggestions'] ?? '';
        $purchaseRequests = $config['purchase_requests'] ?? '';
        $systemErrors = $config['system_errors'] ?? '';

        $usersIds = $users ? $users->pluck('telegram_id')->toArray() : [];

        $ids = match ($this) {
            self::FA_FA => [$ttChat],
            self::EXPORT_USERS => [$welcome],
            self::LIST_BIRTHDAYS => [$welcome],
            self::REGISTRATION => [$welcome],
            self::CHANGE_USER => [$welcome],
            self::MONO_UNMATCHED_PAYMENT => [$welcome],
            self::SUGGESTION => [$suggestions],
            self::PURCHASE_REQUEST => [$purchaseRequests],

            self::STATS_MENTION => [$ttChat],
            self::DAILY_DIGEST => [$ttChat],
            self::DAILY_DIGEST_COLLECT => [$ttChat],
            self::MEMBERSHIP => [$ttChat],
            self::NEW_MEMBER_WELCOME => [$ttChat],

            self::TEST => [$testBot2],
            self::SYSTEM_ERRORS => [$systemErrors],

            self::MY => $myIds,
            self::USERS => $usersIds,
            self::CUSTOM => $chantIds,
        };

        if ($isLocal && $this !== self::TEST && $this !== self::SYSTEM_ERRORS) {
            $ids = array_filter($ids) === [] ? [] : [$testBot2];
        }

        return $ids;
    }

    /**
     * Чи входить чат до списку цього події (порожні/ненастроєні id не збігаються ні з чим).
     */
    public function hasChat(int|string|null $chatId): bool
    {
        if ($chatId === null || $chatId === '') {
            return false;
        }

        $ids = array_map('strval', array_filter($this->getIds()));

        return in_array((string) $chatId, $ids, true);
    }
}
