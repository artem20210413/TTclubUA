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

    /**
     * Получить количество ПОЛУЧЕННЫХ упоминаний (где я владелец).
     */
    public function getReceivedMentionsCount(): JsonResponse
    {
        $user = Auth::user();

        $count = Mention::query()
            ->whereHas('carOwnerUser', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->count();

        return response()->json([
            'mentions_count' => $count,
        ]);
    }

    /**
     * Получить список ПОЛУЧЕННЫХ упоминаний.
     */
    public function getReceivedMentions(): JsonResponse
    {
        $user = Auth::user();

        $mentions = Mention::query()
            ->whereHas('carOwnerUser', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->latest()
            ->paginate(20);

        return response()->json($mentions);
    }

    /**
     * Получить количество ОТПРАВЛЕННЫХ упоминаний (где я автор).
     */
    public function getSentMentionsCount(): JsonResponse
    {
        $user = Auth::user();

        $count = Mention::query()
            ->where('owner_id', $user->id)
            ->count();

        return response()->json([
            'sent_mentions_count' => $count,
        ]);
    }

    /**
     * Получить список ОТПРАВЛЕННЫХ упоминаний.
     */
    public function getSentMentions(): JsonResponse
    {
        $user = Auth::user();

        $mentions = Mention::query()
            ->where('owner_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json($mentions);
    }
}
