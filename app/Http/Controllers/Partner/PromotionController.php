<?php

namespace App\Http\Controllers\Partner;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partners\StorePromotionRequest;
use App\Http\Requests\Partners\UpdatePromotionRequest;
use App\Http\Resources\PromotionResource;
use App\Models\Partner;
use App\Models\Promotion;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PromotionController extends Controller
{
    public function index(Request $request, Partner $partner): JsonResponse
    {
        $query = $partner->promotions()->getQuery();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->boolean('active_now')) {
            $now = now();
            $query->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $now);
            })->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $now);
            });
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('promo_title', 'like', "%$search%")
                    ->orWhere('promo_description', 'like', "%$search%");
            });
        }

        $query->orderBy('priority', 'desc')->orderBy('created_at', 'desc');

        $promotions = $query->paginate($request->input('per_page', 15));

        return success(data: PromotionResource::collection($promotions));
    }

    public function store(StorePromotionRequest $request, Partner $partner): JsonResponse
    {
        DB::beginTransaction();
        try {
            $promotion = $partner->promotions()->create($request->validated());

            if ($request->hasFile('photos')) {
                $imageWebp = new ImageWebpService(...$request->file('photos'));
                $imageWebp->convert(EnumImageQuality::HD);
                $imageWebp->save($promotion, EnumTypeMedia::PHOTO_PARTNER_PROMOTION);
            }

            DB::commit();

            return success(data: new PromotionResource($promotion), status: 201);
        } catch (Throwable $e) {
            DB::rollBack();
            throw new ApiException('Під час створення акції сталася помилка.', 500);
        }
    }

    public function show(Partner $partner, Promotion $promotion): JsonResponse
    {
        try {

            if ($promotion->partner_id !== $partner->id) {
                throw new ApiException('Акцію не знайдено для цього партнера.', 404);
            }
            return success(data: new PromotionResource($promotion));
        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function update(UpdatePromotionRequest $request, Partner $partner, Promotion $promotion): JsonResponse
    {
        if ($promotion->partner_id !== $partner->id) {
            throw new ApiException('Акцію не знайдено для цього партнера.', 404);
        }

        $promotion->update($request->validated());

        return success(data: new PromotionResource($promotion));
    }

    public function destroy(Partner $partner, Promotion $promotion): JsonResponse
    {
        if ($promotion->partner_id !== $partner->id) {
            throw new ApiException('Акцію не знайдено для цього партнера.', 404);
        }

        $promotion->delete();

        return success(message: 'Акцію успішно видалено.');
    }

    public function addPhotos(Request $request, Partner $partner, Promotion $promotion): JsonResponse
    {
        try {

            if ($promotion->partner_id !== $partner->id) {
                throw new ApiException('Акцію не знайдено для цього партнера.', 404);
            }

            $request->validate([
                'photos' => 'required|array',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif,heic,heif|max:20000',
            ]);

            $imageWebp = new ImageWebpService(...$request->file('photos'));
            $imageWebp->convert(EnumImageQuality::HD);
            $imageWebp->save($promotion, EnumTypeMedia::PHOTO_PARTNER_PROMOTION);

            return success(data: new PromotionResource($promotion));
        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function deletePhoto(Partner $partner, Promotion $promotion, int $mediaId): JsonResponse
    {
        try {
            if ($promotion->partner_id !== $partner->id) {
                throw new ApiException('Акцію не знайдено для цього партнера.', 404);
            }

            $media = $promotion->getMedia(EnumTypeMedia::PHOTO_PARTNER_PROMOTION->value)->firstWhere('id', $mediaId);
            if (!$media) {
                throw new ApiException('Фото не знайдено.', 404);
            }
            $media->delete();

            return success(data: new PromotionResource($promotion->refresh()));
        } catch (ApiException $e) {
            return error($e);
        }
    }

}
