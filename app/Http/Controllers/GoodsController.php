<?php

namespace App\Http\Controllers;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Resources\EventResource;
use App\Http\Resources\GoodsResource;
use App\Models\Event;
use App\Models\Goods;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    public function lists(Request $request)
    {
        $q = Goods::query();

        if ($title = $request->input('title')) {
            $title = trim($title);
            $q->where('title', 'like', "%$title%");
        }

        $active = $request->input('active');
        if ($active !== null) {
            $q->where('active', $active);
        }

        $q->orderBy('priority', 'desc');

        return success(data: GoodsResource::collection($q->paginate($request->per_page ?? 10)));
    }

    public function create(Request $request)//TODO validation
    {
        $goods = new Goods();
        $goods->title = $request->input('title');
        $goods->description = $request->input('description');
        $goods->price = $request->input('price');
        $goods->priority = $request->input('priority');
        $goods->save();

        return success(data: new GoodsResource($goods));
    }

    public function update(Goods $goods, Request $request)//Todo validation
    {
        $goods->title = $request->input('title');
        $goods->description = $request->input('description');
        $goods->price = $request->input('price');
        $goods->active = $request->input('active');
        $goods->priority = $request->input('priority');
        $goods->save();

        return success(data: new GoodsResource($goods));
    }

    public function changeActive(Goods $goods, int $active)
    {
        $goods->active = $active;
        $goods->save();

        return success(data: new GoodsResource($goods));
    }


    public function eventAddImage(Goods $goods, Request $request)
    {
        if ($file = $request->file('file')) {
            $imageWebp = new ImageWebpService($file);
            $imageWebp->convert(EnumImageQuality::HD);
            $imageWebp->save($goods, EnumTypeMedia::PHOTO_GOODS);
        }

        return success(data: new GoodsResource($goods));
    }

    public function eventDeleteImage(Goods $goods, int $mediaId)
    {
        try {
            $media = $goods->getMedia(EnumTypeMedia::PHOTO_GOODS->value)
                ->where('id', $mediaId)
                ->first();

            if (!$media) {
                throw new ApiException('Фото не знайдено');
            }

            $media->delete();

            return success(data: new GoodsResource($goods));
        } catch (ApiException $e) {
            return error($e->getCode());
        }

    }

    public function eventDeleteImages(Goods $goods)
    {
        $goods->clearMediaCollection(EnumTypeMedia::PHOTO_GOODS->value);

        return success(data: new GoodsResource($goods));
    }

}
