<?php

namespace App\Http\Controllers;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Resources\CityResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventTypeResource;
use App\Http\Resources\PublicationResource;
use App\Http\Resources\PublicationTypeResource;
use App\Models\City;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Mention;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\Request;


class EventController extends Controller
{


    public function list(Request $request)
    {
        $q = Event::query();

        if ($event_type_id = $request->type)
            $q->where('event_type_id', $event_type_id);

        if ($title = $request->search) {
            $title = trim($title);
            $q->where('title', 'like', "%$title%");
        }

        if (!is_null($request->active)) {
            $q->where('active', (bool)$request->active);
        }

        if ($date_from = $request->date_from) {
            $q->whereDate('event_date', '>=', $date_from);
        }
        if ($date_to = $request->date_to) {
            $q->whereDate('event_date', '<=', $date_to);
        }

        $q->orderByDesc('created_at');

        return success(data: EventResource::collection($q->paginate($request->per_page ?? 10)));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'event_type_id' => 'required|exists:event_types,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'place' => 'required|string|max:255',
            'event_date' => 'required|date',
            'google_maps_url' => 'nullable|url',
        ]);
        $validated['active'] = 1;

        // Создаём событие, user_id автоматически запишется в модели через boot()
        $event = Event::create($validated);

        return success(data: new EventResource($event));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'event_type_id' => 'sometimes|required|exists:event_types,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'place' => 'sometimes|required|string|max:255',
            'event_date' => 'sometimes|required|date',
            'google_maps_url' => 'nullable|url',
            'active' => 'nullable|boolean',
        ]);

        $event->update($validated);

        return success(data: new EventResource($event));
    }

    public function changeActive(Event $event, int $active)
    {
        $event->active = $active;
        $event->save();

        return success(data: new EventResource($event));
    }

    public function eventAddImage(Event $event, Request $request)
    {
        if ($file = $request->file('file')) {
            $imageWebp = new ImageWebpService($file);
            $imageWebp->convert(EnumImageQuality::HD);
            $imageWebp->save($event, EnumTypeMedia::PHOTO_EVENT);
        }

        return success(data: new EventResource($event));
    }

    public function eventDeleteImages(Event $event)
    {
        $event->clearMediaCollection(EnumTypeMedia::PHOTO_EVENT->value);

        return success(data: new EventResource($event));
    }

    public function eventDeleteImage(Event $event, int $mediaId)
    {
        $media = $event->getMedia(EnumTypeMedia::PHOTO_EVENT->value)->firstWhere('id', $mediaId);
        if (!$media) throw new ApiException('Фото не знайдено.', 0, 400);
        $media->delete();

        return success(data: new EventResource($event));
    }

    public function type()
    {
        return success(data: EventTypeResource::collection(EventType::all()));
    }

    public function eventTypeAddImage(EventType $eventType, Request $request)
    {
        if ($file = $request->file('file')) {
            $imageWebp = new ImageWebpService($file);
            $imageWebp->convert(EnumImageQuality::FULL_HD);
            $eventType->clearMediaCollection(EnumTypeMedia::PHOTO_EVENT_TYPE->value);
            $imageWebp->save($eventType, EnumTypeMedia::PHOTO_EVENT_TYPE);
        }


        return success(data: new EventTypeResource($eventType));
    }

    public function eventTypeDeleteImages(EventType $eventType)
    {
        $eventType->clearMediaCollection(EnumTypeMedia::PHOTO_EVENT_TYPE->value);

        return success(data: new EventTypeResource($eventType));
    }

    /**
     * Отображает публичную страницу события для шеринга.
     *
     * @param Event $event
     * @return \Illuminate\Contracts\View\View
     */
    public function showPublic(Event $event)
    {
        if (!$event->active) abort(404);

        return view('events.show', ['event' => $event]);
    }
}
