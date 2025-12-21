<?php

namespace App\Http\Controllers\Partner;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partners\StorePartnerRequest;
use App\Http\Requests\Partners\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PartnerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Partner::query()->withCount('promotions');

        // Фильтр по активности
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Фильтр "активные сейчас"
        if ($request->boolean('active_now')) {
            $now = now();
            $query->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $now);
            })->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $now);
            });
        }

        // Фильтр "только с акциями"
        if ($request->boolean('with_promotions')) {
            $query->whereHas('promotions');
        }

        // Поиск по названию и описанию
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        // Сортировка: сначала по приоритету, затем по дате создания
        $query->orderBy('priority', 'desc')->orderBy('created_at', 'desc');

        $partners = $query->paginate($request->input('per_page', 15));

        return success(data: PartnerResource::collection($partners));
    }

    public function store(StorePartnerRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $partner = Partner::create($request->validated());

            if ($request->hasFile('photos')) {
                $imageWebp = new ImageWebpService(...$request->file('photos'));
                $imageWebp->convert(EnumImageQuality::HD);
                $imageWebp->save($partner, EnumTypeMedia::PHOTO_PARTNER);
            }

            DB::commit();
            return success(data: new PartnerResource($partner), status: 201);
        } catch (ApiException $e) {
            DB::rollBack();
            return error($e);
        } catch (Throwable $e) {
            DB::rollBack();
            return $e;
        }
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): JsonResponse
    {
        $partner->update($request->validated());

        return response()->json(new PartnerResource($partner));
    }

    public function addPhotos(Partner $partner, Request $request): JsonResponse
    {
        $request->validate([
//            'photos' => 'required|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,heic,heif|max:24576',
        ]);

        try {

            $imageWebp = new ImageWebpService(...$request->file('photos'));
            $imageWebp->convert(EnumImageQuality::HD);
            $imageWebp->save($partner, EnumTypeMedia::PHOTO_PARTNER);

            return success(data: new PartnerResource($partner));
        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function deletePhoto(Partner $partner, int $mediaId): JsonResponse
    {
        try {
            $media = $partner->getMedia(EnumTypeMedia::PHOTO_PARTNER->value)->firstWhere('id', $mediaId);
            if (!$media) {
                throw new ApiException('Фото не знайдено.', 404);
            }
            $media->delete();

            return success(data: new PartnerResource($partner->refresh()));
        } catch (ApiException $e) {
            return error($e);
        }
    }

}
