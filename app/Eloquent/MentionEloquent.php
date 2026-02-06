<?php

namespace App\Eloquent;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Requests\MentionRequest;
use App\Models\Car;
use App\Models\Color;
use App\Models\Mention;
use App\Models\User;
use App\Services\Image\ImageWebpService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;

class MentionEloquent
{

    public static function create(Car $car, User $owner, ?string $description, ?UploadedFile $file): Mention
    {

        $mention = new Mention();
        $mention->owner_id = $owner->id;
        $mention->car_id = $car->id;
        $mention->description = $description;
        $mention->save();

        if ($file) {
            $imageWebp = new ImageWebpService($file);
            $imageWebp->convert(EnumImageQuality::HD);
            $imageWebp->save($mention, EnumTypeMedia::PHOTO_MENTION);
        }

        return $mention;
    }

//    public static function create(Car $car, MentionRequest $request): Mention
//    {
//
//        $owner = auth()->user();
//
//        $description = $request->description;
//
//
//        $mention = new Mention();
//        $mention->owner_id = $owner->id;
//        $mention->car_id = $car->id;
//        $mention->description = $description;
//        $mention->save();
//
//        if ($file = $request->file('file')) {
//            $imageWebp = new ImageWebpService($file);
//            $imageWebp->convert(EnumImageQuality::HD);
//            $imageWebp->save($mention, EnumTypeMedia::PHOTO_MENTION);
//        }
//
//        return $mention;
//}

    public static function delete(Mention $mention): ?bool
    {
        self::fileDelete($mention);

        return $mention->delete();
    }

    public static function fileDelete(Mention $mention): void
    {
        if ($mention->hasMedia(EnumTypeMedia::PROFILE_PICTURE->value)) {
            $mention->getMedia(EnumTypeMedia::PROFILE_PICTURE->value)->each->delete();
        }
    }


    /** statistic */
    /**
     * @param $startOfMonth
     * @return string
     * Найактивніший день тижня
     */
    public static function getMostActiveDayWeek($startOfMonth)
    {
        $result = Mention::where('created_at', '>=', $startOfMonth)
            ->select(\DB::raw('DAYNAME(created_at) as day'), \DB::raw('count(*) as count'))
            ->groupBy('day')
            ->orderByDesc('count')
            ->first();

        if (!$result) {
            return '—';
        }
        return \Carbon\Carbon::parse($result->date)->locale('uk')->dayName;
    }

    /**
     * Найактивніша конкретна дата за період
     * @param $startOfMonth
     * @return string
     */
    public static function getMostActiveDay($startOfMonth): string
    {
        // Шукаємо дату, в яку було зроблено найбільше записів
        $result = Mention::where('created_at', '>=', $startOfMonth)
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderByDesc('count')
            ->first();

        if (!$result) {
            return '—';
        }

        return \Carbon\Carbon::parse($result->date)
            ->locale('uk')
            ->isoFormat('D MMMM');
    }

    /**
     * @param $startOfMonth
     * @return mixed
     * Найпопулярніший колір
     */
    public static function getMostSpottedColor($startOfMonth): ?Color
    {
        // Отримуємо статистику по ID кольору
        $stat = Mention::join('cars', 'mentions.car_id', '=', 'cars.id')
            ->where('mentions.created_at', '>=', $startOfMonth)
            ->select('cars.color_id', \DB::raw('count(*) as count'))
            ->groupBy('cars.color_id')
            ->orderByDesc('count')
            ->first();

        if (!$stat || !$stat->color_id) return null;

        // Знаходимо модель кольору
        $color = \App\Models\Color::find($stat->color_id);

        if ($color) {
            // Додаємо кількість згадок в об'єкт моделі
            $color->mentions_count = $stat->count;
        }

        return $color;
    }

    /**
     * @param $startOfMonth
     * @return mixed
     * ТОП «Мисливець» (найактивніший споттер)
     */
    public static function getTopHunter($startOfMonth): ?User
    {
        // Отримуємо статистику
        $stat = Mention::where('created_at', '>=', $startOfMonth)
            ->select('owner_id', \DB::raw('count(*) as count'))
            ->groupBy('owner_id')
            ->orderByDesc('count')
            ->first();

        if (!$stat) return null;

        // Отримуємо модель користувача
        $user = $stat->owner;

        if ($user) {
            // Записуємо кількість прямо в об'єкт
            $user->mentions_count = $stat->count;
        }

        return $user;
    }

    /**
     * ТОП-3 найпомітніших автомобілів за період
     * @param $startOfMonth
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTopCars($startOfMonth, int $count = 3): Collection
    {
        // Отримуємо статистику для топ-3
        $stats = Mention::where('created_at', '>=', $startOfMonth)
            ->select('car_id', \DB::raw('count(*) as mentions_count'))
            ->groupBy('car_id')
            ->orderByDesc('mentions_count')
            ->limit($count)
            ->get();

        // Перетворюємо статистику в колекцію моделей Car з доданим показником count
        return $stats->map(function ($stat) {
            $car = $stat->car;
            if ($car) {
                $car->mentions_count = $stat->mentions_count;
            }
            return $car;
        })->filter(); // filter прибере null, якщо раптом машина була видалена з бази
    }

    /**
     * @param $startOfMonth
     * @return mixed
     * Підрахунок загальної кількості згадок за місяць
     */
    public static function getTotalMentions($startOfMonth)
    {
        return Mention::where('created_at', '>=', $startOfMonth)->count();
    }
}
