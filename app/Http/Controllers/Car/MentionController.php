<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Http\Requests\MentionRequest;
use App\Jobs\SandMention;
use App\Models\Car;
use App\Models\Mention;
use App\Models\User;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
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


    public function getSentMentionsCount(): JsonResponse
    {
        $user = Auth::user();

        $count = Mention::query()
            ->where('owner_id', $user->id)
            ->count();

        return response()->json([
            'mentions_count' => $count,
        ]);
    }

    public function getSentMentions(): JsonResponse
    {
        $user = Auth::user();

        // Предполагается, что в модели Mention есть отношения owner() и car()
        $mentions = Mention::query()
            ->where('owner_id', $user->id)
            ->latest() // Сортируем по дате (сначала новые)
            ->paginate(20); // Добавляем пагинацию


//        dd($mentions[1]->carOwnerUser);
        return response()->json($mentions);
    }

}

