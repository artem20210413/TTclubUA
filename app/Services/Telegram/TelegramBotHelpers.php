<?php

namespace App\Services\Telegram;

use App\Models\Car;
use App\Models\Registration;
use App\Models\User;
use App\Services\Telegram\Dto\ChatMember\EnumTelegramChatMemberStatus;
use App\Services\Telegram\Dto\TelegramUserDto;
use App\Services\Telegram\Dto\TelegramWebhookDto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class TelegramBotHelpers
{
    /**
     * Resolves a Telegram message template by translation key path and substitutes {token} placeholders.
     * Falls back to $default when the translation key is not found, matching the pre-refactor
     * config('telegram.messages.<key>', $default) behavior.
     */
    public static function renderTemplate(string $key, array $replacements = [], string $default = '---'): string
    {
        $template = Lang::has('telegram.'.$key) ? __('telegram.'.$key) : $default;

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public static function getNewMemberWelcomeLinks(): array
    {
        return Lang::has('telegram.new_member_welcome.links') ? __('telegram.new_member_welcome.links') : [];
    }

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
        if ($user?->telegram_id) {
            return self::MentionPerson($user);
        }

        if ($user?->telegram_nickname) {
            return self::LinkToPerson($user);
        }

        return $user?->name;
    }

    public static function generationTextMention(User $owner, Car $car, ?string $description, ?Carbon $time = null): string
    {
        $text = self::renderTemplate('fa_fa', [
            '{owner}' => self::TryMentionPerson($owner),
            '{employee}' => self::TryMentionPerson($car?->user),
        ]);

        if ($time) {
            $text = $text."\n".$time->toDateTimeString();
        }
        if ($description) {
            $text = $text."\n\n✍️: $description";
        }

        return $text;
    }

    public static function generationTextSuggestion(User $user, string $description, ?string $environment): string
    {
        return self::renderTemplate('new_suggestion', [
            '{user}' => self::TryMentionPerson($user),
            '{phone}' => $user->phone,
            '{description}' => $description,
            '{environment_line}' => $environment ?? '-',
        ]);
    }

    public static function generationTextRegistration(Registration $registration): string
    {
        $data = $registration->getJson();
        $cities = collect($data->cities_model)
            ->map(fn ($city) => "{$city->name} ({$city->country})")
            ->implode(', ');

        $why_tt_short = Str::limit($data->why_tt, 50, '...');
        $occupation_short = Str::limit($data->occupation_description, 50, '...');
        $user = self::renderTemplate('registration.user', [
            '{name}' => $data->name,
            '{phone}' => $data->phone,
            '{cities}' => $cities,
            '{birth_date}' => $data->birth_date,
            '{telegram_nickname}' => $data->telegram_nickname,
            '{instagram_nickname}' => $data->instagram_nickname,
            '{occupation_description}' => $occupation_short,
            '{mail_address}' => $data->mail_address,
            '{why_tt}' => $why_tt_short,
            '{created_at}' => $registration->created_at->format('d.m.Y H:i'),
        ]);

        $cars = '';

        if (isset($data->car)) {
            $car = $data->car;
            $cars .= self::renderTemplate('registration.car', [
                '{model}' => $car->model->name,
                '{gene}' => $car->gene->name,
                '{color}' => $car->color->name,
                '{license_plate}' => $car->license_plate,
                '{personalized_license_plate}' => $car->personalized_license_plate ?? '—',
            ]);
        }

        $noCarText = self::renderTemplate('registration.without_car', [], 'Немає Audi TT.');
        $cars = $cars === '' ? $noCarText : $cars;

        return $user."\n\n".$cars;
    }

    public static function generationTextAuthCode(string $code, int $minutes): string
    {
        return self::renderTemplate('auth_code', [
            '{code}' => $code,
            '{minutes}' => $minutes,
        ]);
    }

    public static function generationTextNewUser(array $getNewChatMembers)
    {
        foreach ($getNewChatMembers as $member) {
            /** @var TelegramUserDto $member */
            return self::renderTemplate('new_member_welcome.text', [
                '{member}' => "<a href='tg://user?id={$member->getId()}'>{$member->getFirstName()}</a>",
            ]);
        }

        return '';
    }

    public static function generationTextNewUserLog(TelegramWebhookDto $telegramWebhookDto)
    {
        $who = $telegramWebhookDto->getMessage()->getFrom();
        foreach ($telegramWebhookDto->getMessage()->getNewChatMembers() as $member) {
            $whom[] = "<a href='tg://user?id={$member->getId()}'>{$member->getFirstName()}</a>";
        }
        $whom = implode(', ', $whom ?? []);
        $where = $telegramWebhookDto->getSmartChat()->getSmartTitle();

        return self::renderTemplate('new_member_welcome_log', [
            '{who}' => "<a href='tg://user?id={$who->getId()}'>{$who->getFirstName()}</a>",
            '{whom}' => $whom,
            '{where}' => $where,
        ], 'generationTextNewUserLog');
    }

    public static function generationTextChangeNickname(?string $oldNickname, ?string $newNickname): string
    {
        $oldDisplay = $oldNickname ?? 'відсутнього';
        $newDisplay = $newNickname ?? 'порожній';

        return self::renderTemplate('change_nickname', [
            '{old_nickname}' => $oldDisplay,
            '{new_nickname}' => $newDisplay,
        ], 'Нікнейм оновлено: {old_nickname} -> {new_nickname}');
    }

    public static function generationTextUserLeftLog(TelegramWebhookDto $telegramWebhookDto)
    {
        $key = $telegramWebhookDto->getChatMember()->getNewChatMember()->getStatus() === EnumTelegramChatMemberStatus::LEFT
            ? 'member_left_log'
            : 'member_kicked_log';

        $who = $telegramWebhookDto->getChatMember()->getFromUser();

        $member = $telegramWebhookDto->getChatMember()->getNewChatMember()->getUser();
        $whom = "<a href='tg://user?id={$member->getId()}'>{$member->getFirstName()}</a>";

        $where = $telegramWebhookDto->getSmartChat()->getSmartTitle();

        return self::renderTemplate($key, [
            '{who}' => "<a href='tg://user?id={$who->getId()}'>{$who->getFirstName()}</a>",
            '{whom}' => $whom,
            '{where}' => $where,
        ], 'generationTextNewUserLog');
    }
}
