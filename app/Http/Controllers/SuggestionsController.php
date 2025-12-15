<?php

namespace App\Http\Controllers;

use App\Enum\EnumTelegramEvents;
use App\Http\Requests\SuggestionsRequest;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuggestionsController extends Controller
{
    public function send(SuggestionsRequest $request)
    {
        $user = Auth::user();
        $description = $request->input('description');
        $environment = $request->input('environment');
        $photos = $request->file('files');

        $bot = new TelegramBot(EnumTelegramEvents::SUGGESTION);

        $message = TelegramBotHelpers::generationTextSuggestion($user, $description, $environment);

        if ($photos) {
            $photoPaths = [];
            foreach ($photos as $photo) {
                $photoPaths[] = $photo->getRealPath();
            }
            $bot->sendPhotosPathOrUrlWithDescription($photoPaths, $message);
        } else {
            $bot->sendMessage($message);
        }

        return response()->json(['message' => 'Ваше звернення успішно відправлено!']);
    }
}
