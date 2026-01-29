<?php

namespace App\Services\Telegram;

use App\Models\Car;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Monolog\Handler\IFTTTHandler;
use function Laravel\Prompts\confirm;

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
            $text = $text . "\n" . $time->toDateTimeString();
        }
        if ($description) {
            $text = $text . "\n\n✍️: $description";
        }

        return $text;
    }

    public static function generationTextSuggestion(User $user, string $description, ?string $environment): string
    {
//        $text = "📢 <b>Нове звернення!</b>\n"
//            . "<b>Від:</b> {user}\n"
//            . "📞<b>:</b> {phone}\n"
//            . "⚙️<b>:</b> {environment_line}\n"
//            . "📄<b>:</b> {description}";
        $text = config('telegram.messages.new_suggestion', '---');

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

        $userTemplate = config('telegram.messages.registration.user', '---');

        $user = str_replace(
            [
                '{name}',
                '{phone}',
                '{cities}',
                '{birth_date}',
                '{telegram_nickname}',
                '{instagram_nickname}',
                '{occupation_description}',
                '{mail_address}',
                '{why_tt}',
                '{created_at}'
            ],
            [
                $data->name,
                $data->phone,
                $cities,
                $data->birth_date,
                $data->telegram_nickname,
                $data->instagram_nickname,
                $data->occupation_description,
                $data->mail_address,
                $data->why_tt,
                $registration->created_at->format('d.m.Y H:i')
            ],
            $userTemplate
        );

        $cars = '';
        $carTemplate = config('telegram.messages.registration.car', '---'
        );

        if (isset($data->car)) {
            $car = $data->car;
            $cars .= str_replace(
                [
                    '{model}',
                    '{gene}',
                    '{color}',
                    '{license_plate}',
                    '{personalized_license_plate}'
                ],
                [
                    $car->model->name,
                    $car->gene->name,
                    $car->color->name,
                    $car->license_plate,
                    $car->personalized_license_plate ?? '—'
                ],
                $carTemplate
            );
        }

        $noCarText = config('telegram.messages.registration.without_car', 'Немає Audi TT.');
        $cars = $cars === "" ? $noCarText : $cars;

        return $user . "\n\n" . $cars;
    }

    public static function generationTextAuthCode(string $code, int $minutes): string
    {
        $template = config('telegram.messages.auth_code', '---');

        return str_replace(
            ['{code}', '{minutes}'],
            [$code, $minutes],
            $template
        );
    }

}
