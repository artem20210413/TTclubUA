<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Http\Requests\MentionRequest;
use App\Jobs\SandMention;
use App\Models\Car;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SebastianBergmann\Timer\Timer;


class MentionController extends Controller
{

    public function mention(Car $car, MentionRequest $request)
    {

        $path = $request->file('file')
            ? Storage::put('temporary-files/mentions', $request->file('file'))
            : 'nane';

        SandMention::dispatch($car, $path, $request->description, $request->user(), Carbon::now());

//        $description = $request->description;
//        $user = $request->user();
//        $time  = Carbon::now();
//        $storagePath = storage_path('app/private/' . $path);
//        $file = new UploadedFile($storagePath, basename($storagePath), mime_content_type($storagePath), null, true);
//
//        $mention = MentionEloquent::create($car, $user, $description, $file);
//
//        Storage::delete($path);
//
//        $imageUrl = $mention->getFirstMedia(EnumTypeMedia::PHOTO_MENTION->value)?->getPath();
//        $text = TelegramBotHelpers::generationTextMention($user, $car, $description, $time);
//        $bot = new TelegramBot(EnumTelegramChats::MENTION);
//
//        if ($imageUrl) {
//            $bot->sendPhotoAndDescription($imageUrl, $text);
//        } else {
//            $bot->sendMessage($text);
//        }

        return success();
    }


//    public function mentionOld(Car $car, MentionRequest $request)
//    {
//        $mention = MentionEloquent::create($car, $request->user(), $request->description, $request->file('file'));
//        $imageUrl = $mention->getFirstMedia(EnumTypeMedia::PHOTO_MENTION->value)?->getPath();
//
//        $text = TelegramBotHelpers::generationTextMention($request->user(), $car, $request->description, Carbon::now());
//        $bot = new TelegramBot(EnumTelegramChats::MENTION);
//
//        if ($imageUrl) {
//            $bot->sendPhotoAndDescription($imageUrl, $text);
//        } else {
//            $bot->sendMessage($text);
//        }
//
//
//        return success();
//
//    }
}

class SimpleTimer
{
    protected float $start;
    protected array $marks = [];

    public function start(): void
    {
        $this->start = microtime(true);
    }

    public function mark(string $label): void
    {
        $now = microtime(true);
        $elapsed = round($now - $this->start, 4);
        dump("{$label}. Elapsed: {$elapsed}s");
        $this->start = $now;
    }

    //$timer = new SimpleTimer();
    //$timer->start();
    //        $timer->mark('Create TelegramBot');
}
