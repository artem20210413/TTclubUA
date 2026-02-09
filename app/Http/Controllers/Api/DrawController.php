<?php

namespace App\Http\Controllers\Api;

use App\Enum\DrawStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Draw\StoreDrawRequest;
use App\Http\Requests\Draw\UpdateDrawRequest;
use App\Http\Resources\DrawResource;
use App\Http\Resources\PrizeResource;
use App\Models\Draw;
use App\Models\Prize;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DrawController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $draws = Draw::orderBy('created_at', 'desc')->paginate(15);

        return DrawResource::collection($draws);
    }

    public function show(Draw $draw): DrawResource
    {
        $draw->with(['participants.user', 'prizes']);

        return new DrawResource($draw);
    }

    public function store(StoreDrawRequest $request): DrawResource
    {
        $draw = Draw::create($request->validated());

        return new DrawResource($draw);
    }

    public function update(UpdateDrawRequest $request, Draw $draw): DrawResource
    {
        $draw->update($request->validated());

        return new DrawResource($draw);
    }


    public function rollPrize(Draw $draw, Prize $prize): PrizeResource
    {
        try {

            // Перевірки
            if ($prize->draw_id !== $draw->id) {
                abort(404, 'Приз не належить до цього розіграшу.');
            }
            if ($prize->winner_participant_id) {
                throw new ApiException('Цей приз вже було розіграно.', 100, 400);
            }

            $participantsQuery = $draw->participants();

            // Якщо не дозволено вигравати кілька разів, виключаємо переможців
            if (!$draw->allow_multiple_wins) {
                $participantsQuery->where('is_winner', false);
            }

            $participants = $participantsQuery->get();

            if ($participants->isEmpty()) {
                throw new ApiException('Немає доступних учасників для розіграшу.', 100, 400);
            }

            // Створюємо "рулетку" з урахуванням ваги
//            $roulette = [];
//            foreach ($participants as $participant) {
//                for ($i = 0; $i < $participant->weight; $i++) {
//                    $roulette[] = $participant->id;
//                }
//            }
//            $winnerId = $roulette[array_rand($roulette)];

            // Створюємо "рулетку" з урахуванням ваги
            $weightedList = collect();
            foreach ($participants as $p) {
                for ($i = 0; $i < $p->weight; $i++) {
                    $weightedList->push($p);
                }
            }
            // Вибираємо переможця
            $winnerId = $weightedList->random();
            $winner = $participants->find($winnerId);

            DB::transaction(function () use ($prize, $winner, $draw) {
                // Оновлюємо приз
                $prize->update(['winner_participant_id' => $winner->id]);

                // Позначаємо учасника як переможця
                if (!$draw->allow_multiple_wins) {
                    $winner->update(['is_winner' => true]);
                }

                // Логуємо результат
                $draw->results()->create([
                    'prize_id' => $prize->id,
                    'participant_id' => $winner->id,
                ]);

                // Перевіряємо, чи залишились нерозіграні призи
                $remainingPrizes = $draw->prizes()->whereNull('winner_participant_id')->count();
                if ($remainingPrizes === 0) {
                    $draw->update(['status' => DrawStatus::FINISHED]);
                }
            });

            return new PrizeResource($prize->fresh());
        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function resetPrize(Draw $draw, Prize $prize)
    {
        try {

            if ($prize->draw_id !== $draw->id) {
                abort(404, 'Приз не належить до цього розіграшу.');
            }
            if (!$prize->winner_participant_id) {
                throw new ApiException('Приз ще не було розіграно.', 100, 400);
            }

            $participant = $prize->winner;


            // Знімаємо позначку переможця з учасника, якщо потрібно
            if ($participant && !$draw->allow_multiple_wins) {
                // Перевіряємо, чи є у цього учасника інші виграші в цьому розіграші
                $otherWins = $draw->prizes()->where('winner_participant_id', $participant->id)->where('id', '!=', $prize->id)->count();
                if ($otherWins === 0) {
                    $participant->update(['is_winner' => false]);
                }
            }

            // Очищуємо переможця у приза
            $prize->update(['winner_participant_id' => null]);

            // Якщо розіграш був завершений, повертаємо його в активний статус
            if ($draw->status === DrawStatus::FINISHED) {
                $draw->update(['status' => DrawStatus::ACTIVE]);
            }
            return new PrizeResource($prize->fresh());
        } catch (ApiException $e) {
            return error($e);
        }
    }
}
