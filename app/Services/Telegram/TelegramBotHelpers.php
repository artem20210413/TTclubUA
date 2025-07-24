<?php

namespace App\Services\Telegram;

use App\Models\Car;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;

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
        return self::LinkToPerson($user);
    }

    public static function generationTextMention(User $owner, Car $car, ?string $description, ?Carbon $time = null): string
    {
        $text = "<b>Фа-фа</b>, {employee} - {car}! Лови привітання від {owner}!";

        $text = str_replace("{owner}", self::TryMentionPerson($owner), $text);
        $text = str_replace("{car}", $car->getGeneralShortInfo(), $text);
        $text = str_replace("{employee}", self::TryMentionPerson($car?->user), $text);

        if ($time) {
            $text = $text . "\nДата: " . $time->toDateTimeString();
        }
        if ($description) {
            $text = $text . "\n\n✍️: $description";
        }

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
        foreach ($data->cars as $i => $car) {
            $key = $i + 1;
            $cars .= "🚘 Авто {$car->model->name} {$car->gene->name}:\n"
                . "Колір: {$car->color->name}\n"
                . "Номер: {$car->license_plate}\n"
                . "Індивідуальний номер: " . ($car->personalized_license_plate ?? '—') . "\n\n";
        }

        return $user . "\n\n" . $cars;
    }
}
