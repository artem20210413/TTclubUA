<?php

namespace App\Notifications;

use App\Enum\EnumTypeMedia;
use App\Models\Registration;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class RegistrationNotification extends Notification
{
    public function __construct(private readonly Registration $registration) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $text = $this->generationText();

        $profileImage = $this->registration->getFirstMediaUrl(EnumTypeMedia::PROFILE_PICTURE->value);
        $imageUrls = $this->registration->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value)
            ->map(fn ($media) => $media->getUrl())
            ->toArray();

        if ($profileImage) {
            $imageUrls[] = $profileImage;
        }

        if (empty($imageUrls)) {
            return new TelegramMessagePayload(text: $text);
        }

        return new TelegramMessagePayload(text: $text, mediaGroup: $imageUrls);
    }

    private function generationText(): string
    {
        $data = $this->registration->getJson();
        $cities = collect($data->cities_model)
            ->map(fn ($city) => "{$city->name} ({$city->country})")
            ->implode(', ');

        $whyTtShort = Str::limit($data->why_tt, 50, '...');
        $occupationShort = Str::limit($data->occupation_description, 50, '...');

        $user = TelegramBotHelpers::renderTemplate('registration.user', [
            '{name}' => $data->name,
            '{phone}' => $data->phone,
            '{cities}' => $cities,
            '{birth_date}' => $data->birth_date,
            '{telegram_nickname}' => $data->telegram_nickname,
            '{instagram_nickname}' => $data->instagram_nickname,
            '{occupation_description}' => $occupationShort,
            '{mail_address}' => $data->mail_address,
            '{why_tt}' => $whyTtShort,
            '{created_at}' => $this->registration->created_at->format('d.m.Y H:i'),
        ]);

        $cars = '';

        if (isset($data->car)) {
            $car = $data->car;
            $cars .= TelegramBotHelpers::renderTemplate('registration.car', [
                '{model}' => $car->model->name,
                '{gene}' => $car->gene->name,
                '{color}' => $car->color->name,
                '{license_plate}' => $car->license_plate,
                '{personalized_license_plate}' => $car->personalized_license_plate ?? '—',
            ]);
        }

        $noCarText = TelegramBotHelpers::renderTemplate('registration.without_car', [], 'Немає Audi TT.');
        $cars = $cars === '' ? $noCarText : $cars;

        return $user."\n\n".$cars;
    }
}
