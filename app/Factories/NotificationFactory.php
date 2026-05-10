<?php

namespace App\Factories;

use App\Enum\NotificationsPushType;
use App\Models\Mention;
use App\Models\User;

class NotificationFactory
{
    /**
     * Формування сповіщення про "Фа-Фа" (Mention)
     */
    public static function mentionCreated(Mention $mention): array
    {
        $caughtUser = $mention->caughtUser;
        return [
            'user_id' => $mention->caught_user_id,
            'title' => 'Тебе зловили! 📸',
            'body' => "{$caughtUser->name} помітив твою ТТшку - {$mention->car->getGeneralLicensePlate()}.",
            'type' => NotificationsPushType::FA_FA,
            'data' => [
                'mention_id' => $mention->id,
                'car_id' => $mention->car_id,
            ],
        ];
    }

    /**
     * Формування сповіщення про нове повідомлення (приклад)
     */
    public static function newMessage(User $user, $senderName, $chatId): array
    {
        return [
            'user_id' => $user->id,
            'title' => 'Новое сообщение 💬',
            'body' => "Пользователь $senderName прислал вам сообщение.",
            'type' => NotificationsPushType::MESSAGE,
            'data' => [
                'chat_id' => $chatId,
            ],
        ];
    }
}

