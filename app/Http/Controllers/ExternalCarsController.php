<?php

namespace App\Http\Controllers;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Resources\CityResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventTypeResource;
use App\Http\Resources\ExternalCarResource;
use App\Http\Resources\PublicationResource;
use App\Http\Resources\PublicationTypeResource;
use App\Models\City;
use App\Models\Event;
use App\Models\EventType;
use App\Models\ExternalCar;
use App\Models\Mention;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\Request;


class ExternalCarsController extends Controller
{

    public function getFilterOptions()
    {
        // Отримуємо унікальні значення для кожного поля
        // pluck() вибирає тільки потрібну колонку, unique() прибирає дублі, values() скидає ключі масиву

        $years = ExternalCar::whereNotNull('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $models = ExternalCar::whereNotNull('model_name')
            ->distinct()
            ->orderBy('model_name', 'asc')
            ->pluck('model_name');

        $subCategories = ExternalCar::whereNotNull('sub_category')
            ->distinct()
            ->orderBy('sub_category', 'asc')
            ->pluck('sub_category');

        $colors = ExternalCar::whereNotNull('color_hex')
            ->distinct()
            ->pluck('color_hex');

        // Також можна дістати мін/макс ціну для слайдера у Flutter
        $priceBounds = [
            'min' => (int)ExternalCar::min('price_usd'),
            'max' => (int)ExternalCar::max('price_usd'),
        ];

        return success(data: [
            'years' => $years,
            'models' => $models,
            'sub_categories' => $subCategories,
            'colors' => $colors,
            'price_bounds' => $priceBounds,
        ]);
    }

    public function list(Request $request)
    {
        $q = ExternalCar::query();
        $q = $q->where('is_sold', false);
        $q = $q->where('is_active', true);


        if ($city_name = $request->city_name) {
            $q->where('city_name', 'like', '%' . trim($city_name) . '%');
        }

        if ($search = $request->search) {
            $q->where('title', 'like', '%' . trim($search) . '%');
        }

        if ($year_from = $request->year_from) {
            $q->where('year', '>=', (int)$year_from);
        }

        if ($year_to = $request->year_to) {
            $q->where('year', '<=', (int)$year_to);
        }

        if ($sub_category = $request->sub_category) {
            $q->where('sub_category', trim($sub_category));
        }

        if ($color_hex = $request->color_hex) {
            $color_hex = '#' . ltrim($color_hex, '#');
            $q->where('color_hex', $color_hex);
        }
        if ($model_name = $request->model_name) {
            $q->where('model_name', trim($model_name));
        }

        if ($price_from = $request->price_from) {
            $q->where('price_usd', '>=', (int)$price_from);
        }

        if ($price_to = $request->price_to) {
            $q->where('price_usd', '<=', (int)$price_to);
        }

        $q->orderByDesc('created_at');

        return success(data: ExternalCarResource::collection($q->paginate($request->per_page ?? 15)));
    }

}
