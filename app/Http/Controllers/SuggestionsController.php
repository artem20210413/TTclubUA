<?php

namespace App\Http\Controllers;

use App\Enum\EnumTelegramEvents;
use App\Http\Requests\SuggestionsRequest;
use App\Notifications\SuggestionNotification;
use App\Notifications\Support\TelegramRecipients;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class SuggestionsController extends Controller
{
    public function send(SuggestionsRequest $request)
    {
        $user = Auth::user();
        $description = $request->input('description');
        $environment = $request->header('X-Client-Platform', 'unknown');
        $photos = $request->file('files');

        $photoPaths = [];
        if ($photos) {
            foreach ($photos as $photo) {
                $photoPaths[] = $photo->getRealPath();
            }
        }

        Notification::send(
            TelegramRecipients::routes(EnumTelegramEvents::SUGGESTION->getIds()),
            new SuggestionNotification($user, $description, $environment, $photoPaths)
        );

        return response()->json(['message' => 'Ваше звернення успішно відправлено!']);
    }
}
