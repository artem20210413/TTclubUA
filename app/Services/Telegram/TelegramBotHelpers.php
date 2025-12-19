<?php

namespace App\Services\Telegram;

use App\Models\Car;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Monolog\Handler\IFTTTHandler;

class TelegramBotHelpers
{
    public static function MentionPerson(?User $user): string
    {
        $name = $user?->telegram_nickname ?? $user?->name;
        return "<a href='tg://user?id={$user?->telegram_id}'>$name</a>"; // Упоминание
    }

    public static function LinkToPerson(?User $user): string
    {
        return "<a href='https://t.me/{$user?->telegram_nickname}'>$user?->name</a>";
    }

    public static function TryMentionPerson(?User $user): string
    {
        if ($user?->telegram_id)
            return self::MentionPerson($user);

        if ($user?->telegram_nickname)
            return self::LinkToPerson($user);

        return $user?->name;
    }

    public static function generationTextMention(User $owner, Car $car, ?string $description, ?Carbon $time = null): string
    {
//        $text = "<b>Фа-фа!!!</b> {employee} - {car}! Тебе зловили в потоці, лови привітання від {owner}!";
        $text = "<b>Фа-фа!</b> 🚗\n{employee}\nПривіт від {owner} 👋";

        $text = str_replace("{owner}", self::TryMentionPerson($owner), $text);
//        $text = str_replace("{car}", $car->getGeneralShortInfo(), $text);
        $text = str_replace("{employee}", self::TryMentionPerson($car?->user), $text);

        if ($time) {
            $text = $text . "\n🕒: " . $time->toDateTimeString();
        }
        if ($description) {
            $text = $text . "\n\n✍️: $description";
        }

        return $text;
    }

    public static function generationTextSuggestion(User $user, string $description, ?string $environment): string
    {
        $text = "📢 <b>Нове звернення!</b>\n"
            . "<b>Від:</b> {user}\n"
            . "📞<b>:</b> {phone}\n"
            . "⚙️<b>:</b> {environment_line}\n"
            . "📄<b>:</b> {description}";
//        $text = "📬 <b>Нове звернення!</b>\n\n"
//            . "👨‍💻 <b>Від:</b> {user}\n"
//            . "📞 <b>Телефон:</b> {phone}\n"
//            . "⚙️ <b>Середовище:</b> {environment_line}\n\n"
//            . "📄 <b>Повідомлення:</b>\n{description}";


        $text = str_replace("{user}", self::TryMentionPerson($user), $text);
        $text = str_replace("{phone}", $user->phone, $text);
        $text = str_replace("{description}", $description, $text);
        $text = str_replace("{environment_line}", $environment ?? '-', $text);

        return $text;
    }

    public static function generationTextRegistration(Registration $registration): string
    {
        $data = $registration->getJson();
        $cities = collect($data->cities_model)
            ->map(fn($city) => "{$city->name} ({$city->country})")
            ->implode(', ');

        $user = "ім'я: {$data->name}\n"
            . "Телефон: {$data->phone}\n"
            . "Міста: {$cities}\n"
            . "Дата народження: {$data->birth_date}\n"
            . "ТГ: {$data->telegram_nickname} \n"
            . "Інста: {$data->instagram_nickname}\n"
            . "Рід діяльності: {$data->occupation_description}\n"
            . "Адреса НП (для подарунків): {$data->mail_address}\n"
            . "Чому саме ауді ТТ?: {$data->why_tt}\n"
            . "Дата створення: {$registration->created_at->format('d.m.Y H:i')}\n";

        $cars = '';
        if (isset($data->car)) {
            $car = $data->car;
            $cars .= "🚘 Авто {$car->model->name} {$car->gene->name}:\n"
                . "Колір: {$car->color->name}\n"
                . "Номер: {$car->license_plate}\n"
                . "Індивідуальний номер: " . ($car->personalized_license_plate ?? '—') . "\n\n";

        }
        if (isset($data->cars)) {
            //TODO OLD
            foreach ($data->cars as $i => $car) {
                $cars .= "🚘 Авто {$car->model->name} {$car->gene->name}:\n"
                    . "Колір: {$car->color->name}\n"
                    . "Номер: {$car->license_plate}\n"
                    . "Індивідуальний номер: " . ($car->personalized_license_plate ?? '—') . "\n\n";
            }
        }
        $cars = $cars === "" ? 'Немає Audi TT.' : $cars;

        return $user . "\n\n" . $cars;
    }

    public static function generationTextAuthCode(string $code, int $minutes): string
    {
        // шаблон сообщения
        return "<b>Ваш код для входу</b>\n"
            . "<code>$code</code>\n\n"
            . "Код діє $minutes хвилин.";

    }

}
