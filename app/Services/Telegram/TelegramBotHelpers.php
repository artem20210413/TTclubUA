<?php

namespace App\Services\Telegram;

use App\Models\Car;
use App\Models\Registration;
use App\Models\User;
use App\Services\Telegram\Dto\ChatMember\EnumTelegramChatMemberStatus;
use App\Services\Telegram\Dto\TelegramUserDto;
use App\Services\Telegram\Dto\TelegramWebhookDto;
use Carbon\Carbon;
use Illuminate\Support\Str;
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

        $text = config('telegram.messages.fa_fa', '---');

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
        $why_tt_short = Str::limit($data->why_tt, 50, '...');
        $occupation_short = Str::limit($data->occupation_description, 50, '...');
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
                $occupation_short,
                $data->mail_address,
                $why_tt_short,
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

    public static function generationTextNewUser(array $getNewChatMembers)
    {
        foreach ($getNewChatMembers as $member) {
            /** @var TelegramUserDto $member */
            $template = config('telegram.messages.new_member_welcome.text', '---');

            return str_replace(
                ['{member}'],
                ["<a href='tg://user?id={$member->getId()}'>{$member->getFirstName()}</a>"],
                $template
            );
        }

        return '';
    }

    public static function generationTextNewUserLog(TelegramWebhookDto $telegramWebhookDto)
    {
        $template = config('telegram.messages.new_member_welcome_log', 'generationTextNewUserLog');

        $who = $telegramWebhookDto->getMessage()->getFrom();
        foreach ($telegramWebhookDto->getMessage()->getNewChatMembers() as $member) {
            $whom[] = "<a href='tg://user?id={$member->getId()}'>{$member->getFirstName()}</a>";
        }
        $whom = implode(', ', $whom ?? []);
        $where = $telegramWebhookDto->getSmartChat()->getSmartTitle();


        return str_replace(
            ['{who}', '{whom}', '{where}'],
            ["<a href='tg://user?id={$who->getId()}'>{$who->getFirstName()}</a>",
                $whom,
                $where],
            $template
        );
    }

    public static function generationTextChangeNickname(?string $oldNickname, ?string $newNickname): string
    {
        $text = config('telegram.messages.change_nickname', 'Нікнейм оновлено: {old_nickname} -> {new_nickname}');

        $oldDisplay = $oldNickname ?? 'відсутнього';
        $newDisplay = $newNickname ?? 'порожній';

        $text = str_replace("{old_nickname}", $oldDisplay, $text);
        $text = str_replace("{new_nickname}", $newDisplay, $text);

        return $text;
    }

    public static function generationTextUserLeftLog(TelegramWebhookDto $telegramWebhookDto)
    {
        if ($telegramWebhookDto->getChatMember()->getNewChatMember()->getStatus() === EnumTelegramChatMemberStatus::LEFT)
            $template = config('telegram.messages.member_left_log', 'generationTextNewUserLog');
        else
            $template = config('telegram.messages.member_kicked_log', 'generationTextNewUserLog');


        $who = $telegramWebhookDto->getChatMember()->getFromUser();

        $member = $telegramWebhookDto->getChatMember()->getNewChatMember()->getUser();
        $whom = "<a href='tg://user?id={$member->getId()}'>{$member->getFirstName()}</a>";

        $where = $telegramWebhookDto->getSmartChat()->getSmartTitle();

        return str_replace(
            ['{who}', '{whom}', '{where}'],
            ["<a href='tg://user?id={$who->getId()}'>{$who->getFirstName()}</a>",
                $whom,
                $where],
            $template
        );
    }

}
