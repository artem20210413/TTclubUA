<?php

namespace App\Http\Controllers\Car;

use App\Http\Controllers\Controller;
use App\Http\Requests\MentionRequest;
use App\Jobs\SandMention;
use App\Models\Car;
use App\Models\Mention;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


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
     * Получить количество ПОЛУЧЕННЫХ упоминаний (где я владелец машины).
     */
    public function getReceivedMentionsCount(Request $request, User $user): JsonResponse
    {
        $days = $request->input('days');

        $query = Mention::query()
            ->whereHas('carOwnerUser', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            });

        if ($days) {
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        return response()->json([
            'count' => $query->count(),
        ]);
    }

    /**
     * Получить список ПОЛУЧЕННЫХ упоминаний.
     */
    public function getReceivedMentions(Request $request, User $user): JsonResponse
    {
        $days = $request->input('days');

        $query = Mention::query()
            ->whereHas('carOwnerUser', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            });

        if ($days) {
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        $mentions = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json($mentions);
    }

    /**
     * Получить количество ОТПРАВЛЕННЫХ упоминаний (где я owner_id).
     */
    public function getSentMentionsCount(Request $request, User $user): JsonResponse
    {
        $days = $request->input('days');

        $query = Mention::query()->where('owner_id', $user->id);

        if ($days) {
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        return response()->json([
            'count' => $query->count(),
        ]);
    }

    /**
     * Получить список ОТПРАВЛЕННЫХ упоминаний.
     */
    public function getSentMentions(Request $request, User $user): JsonResponse
    {
        $days = $request->input('days');

        $query = Mention::query()
            ->where('owner_id', $user->id);

        if ($days) {
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        $mentions = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json($mentions);
    }
}
