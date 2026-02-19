<?php

namespace App\Http\Controllers\Api;

use App\Enum\DrawStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreParticipantRequest;
use App\Http\Requests\Participant\UpdateParticipantRequest;
use App\Http\Resources\ParticipantResource;
use App\Models\Draw;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ParticipantController extends Controller
{
    public function index(Draw $draw)
    {
        $participants = $draw->participants()->with('user')->paginate(50);

        return success(data: ParticipantResource::collection($participants));
    }

    public function registerSelf(Request $request, Draw $draw)
    {
        try {

            if ($draw->status !== DrawStatus::ACTIVE && $draw->status !== DrawStatus::PLANNED) {
                throw new ApiException('Реєстрація на цей розіграш закрита.', 0, 403);
            }

            if (!$draw->is_public) {
                throw new ApiException('Публічна реєстрація на цей розіграш заборонена.', 0, 403);
            }

            $user = $request->user();

            // Перевірка на дублікат
            $existingParticipant = $draw->participants()->where('user_id', $user->id)->first();
            if ($existingParticipant) {
                throw new ApiException('Ви вже зареєстровані у цьому розіграші.', 0, 400);
            }

            $participant = $draw->participants()->create([
                'user_id' => $user->id,
                'weight' => 1, // Дефолтна вага
            ]);

            return success(data: new ParticipantResource($participant));
        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function registerByAdmin(StoreParticipantRequest $request, Draw $draw)
    {
        $participant = $draw->participants()->create([
            'name_manual' => $request->name_manual,
            'contact_manual' => $request->contact_manual,
            'weight' => $request->weight ?? 1,
        ]);

        return success(data: new ParticipantResource($participant));
    }

    public function update(UpdateParticipantRequest $request, Draw $draw, Participant $participant)
    {
        // Переконаємося, що учасник належить до цього розіграшу
        if ($participant->draw_id !== $draw->id) {
            abort(404);
        }

        $participant->update($request->validated());

        return success(data: new ParticipantResource($participant));
    }

    public function destroy(Draw $draw, Participant $participant): Response
    {
        // Переконаємося, що учасник належить до цього розіграшу
        if ($participant->draw_id !== $draw->id) {
            abort(404);
        }

        $participant->delete();

        return response()->noContent();
    }
}
