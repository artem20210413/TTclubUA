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
//        (new SandMention($car, $path, $request->description, $request->user(), Carbon::now()))->handle();

        return success();
    }

}

