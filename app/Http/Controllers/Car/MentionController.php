<?php

namespace App\Http\Controllers\Car;

use App\Eloquent\CarEloquent;
use App\Eloquent\MentionEloquent;
use App\Enum\EnumImageQuality;
use App\Enum\EnumTelegramChats;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Car\AddCollectionsCarRequest;
use App\Http\Requests\Car\CreateCarRequest;
use App\Http\Requests\Car\UpdateCarRequest;
use App\Http\Requests\MentionRequest;
use App\Http\Resources\Car\CarResource;
use App\Http\Resources\Car\CarWithUserResource;
use App\Http\Resources\Car\GenesResource;
use App\Http\Resources\Car\ModelResource;
use App\Models\Car;
use App\Models\CarGene;
use App\Models\CarModel;
use App\Models\User;
use App\Services\Image\ImageWebpService;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Http\Request;


class MentionController extends Controller
{

    public function mention(Car $car, MentionRequest $request)
    {
        $mention = MentionEloquent::create($car, $request);

        $imageUrl = $mention->getFirstMedia(EnumTypeMedia::PHOTO_MENTION->value)?->getPath();
        $text = TelegramBotHelpers::generationTextMention($request->user(), $car, $request->description);
        $bot = new TelegramBot(EnumTelegramChats::MENTION);

        if ($imageUrl) {
            $bot->sendPhotoAndDescription($imageUrl, $text);
        } else {
            $bot->sendMessage($text);
        }

        return success();

    }
}
