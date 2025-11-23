<?php

namespace App\Http\Controllers;

use App\Enum\EnumTypeMedia;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;


class CalendarController extends Controller
{


    /**
     * GET /calendar?month=2025-02
     *
     * Возвращает события и дни рождения за месяц.
     */
    public function list(Request $request)
    {
        // month в формате Y-m (например "2025-02")
        $monthParam = $request->query('month');

        // если не передан - берем текущий месяц
        $current = $monthParam
            ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : now()->startOfMonth();

        $start = $current->copy()->startOfMonth();
        $end = $current->copy()->endOfMonth();

        // ---- 1. События из events ----
        $events = Event::query()
            ->where('active', 1)
            ->whereBetween('event_date', [$start, $end])
            ->orderBy('event_date')
            ->get();

        $eventItems = $events->map(function (Event $event) {
            $imageUrls = $event->getMedia(EnumTypeMedia::PHOTO_EVENT->value)->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                ];
            });
            return [
                'type' => $event->eventType->alias,
                'id' => 'event-' . $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'date' => optional($event->event_date)->toDateString(),  // 2025-02-14
                'time' => optional($event->event_date)->format('H:i'),   // 19:30
                'place' => $event->place,
                'google_maps' => $event->google_maps_url,
                'images' => $imageUrls,
            ];
        });
        // ---- 2. Дни рождения из users ----
        // Берём всех, у кого birth_date не null и месяц совпадает
        $birthdays = User::query()
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $start->month)
            ->get();

        $birthdayItems = $birthdays->map(function (User $user) use ($start) {
            // День рождения в ТЕКУЩЕМ ГОДУ (для календаря)
            $date = Carbon::createFromDate(
                $start->year,
                $user->birth_date->month,
                $user->birth_date->day
            );

            $images = $user->getMedia(EnumTypeMedia::PROFILE_PICTURE->value)->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                ];
            });
//            $profileImage = $user->getFirstMediaUrl(EnumTypeMedia::PROFILE_PICTURE->value);
//            $images = $profileImage ? [$profileImage] : [];

            return [
                'type' => 'birthday',
                'id' => 'birthday-' . $user->id,
                'title' => $user->name,
                'description' => 'День народження ' . $user->name,
                'date' => $date->toDateString(), // 2025-02-03
                'time' => null,                  // время для ДР можем не задавать
                'place' => null,                  // у ДР нет места
                'google_maps' => null,
                'images' => $images,                    // сюда можно будет прикрутить фото профиля/авто
            ];
        });

        // ---- 3. Объединяем и сортируем ----
        $items = collect()                         // обычная коллекция Support\Collection
        ->merge($eventItems)
            ->merge($birthdayItems)
            ->sortBy(function ($item) {
                // time может быть null (для ДР) — подставим "00:00"
                $time = $item['time'] ?? '00:00';
                return $item['date'] . ' ' . $time;
            })
            ->values()
            ->all();


        return success(data: $items);

//            response()->json([
//            'month' => $start->format('Y-m'),
//            'items' => $items,
//        ]);
    }


}
