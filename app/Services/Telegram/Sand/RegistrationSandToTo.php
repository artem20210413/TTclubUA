<?php

namespace App\Services\Telegram\Sand;

use App\Enum\EnumTelegramChats;
use App\Enum\EnumTypeMedia;
use App\Models\Registration;
use App\Services\Telegram\TelegramBot;

class RegistrationSandToTo
{
    public function __construct(readonly Registration $registration)
    {
        $text = $this->generationText();

        $profileImage = $this->registration->getFirstMediaUrl(EnumTypeMedia::PROFILE_PICTURE->value);
        $imageUrls = $this->registration->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value)->map(function ($media) {
            return $media->getUrl();
        })->toArray();

        if ($profileImage != null)
            $imageUrls[] = $profileImage;

        $bot = new TelegramBot(EnumTelegramChats::NOTIFICATION);

        if (empty($imageUrls)) {
            $bot->sendMessage($text);
        } else {
            $bot->sendPhotosWithDescription($imageUrls, $text);
        }
    }

    public function generationText()
    {
        $data = $this->registration->getJson();
        $cities = collect($data->cities_model)
            ->map(fn($city) => "{$city->name} ({$city->country})")
            ->implode(', ');

        $user = "ім'я: {$data->name}\n"
            . "Телефон: {$data->phone}\n"
            . "Міста: {$cities}\n"
            . "Рід діяльності: {$data->occupation_description}\n"
            . "Дата народження: {$data->birth_date}\n"
            . "ТГ: {$data->telegram_nickname} \n"
            . "Інста: {$data->instagram_nickname}\n"
            . "Дата створення: {$this->registration->created_at->format('d.m.Y H:i')}\n";

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
