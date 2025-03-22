<?php

namespace App\Http\Controllers;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Resources\CityResource;
use App\Http\Resources\PublicationResource;
use App\Http\Resources\PublicationTypeResource;
use App\Models\City;
use App\Models\Mention;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\Request;


class PublicationController extends Controller
{

    public function allType()
    {
        $types = PublicationType::query()->orderBy('sort')->get();

        return success(data: PublicationTypeResource::collection($types));
    }

    public function publication(int $typeId, Request $request)
    {
        $publications = Publication::query()
            ->where('publication_type_id', $typeId)
            ->where('active', 1)
            ->orderBy('updated_at', 'desc')
            ->paginate(50);

        return success(data: PublicationResource::collection($publications));
    }

    public function create(Request $request)
    {
        $publication = Publication::find($request->id) ?: new Publication();
        $publication->user_id = auth()->id();
        $publication->publication_type_id = $request->publication_type_id;
        $publication->title = $request->title;
        $publication->description = $request->description;
        $publication->latitude = $request->latitude;
        $publication->longitude = $request->longitude;
        $publication->active = $request->active;
        $publication->save();

        return success(data: new PublicationResource($publication->refresh()));
    }

    public function addImg(int $publicationId, Request $request)
    {
        $p = Publication::find($publicationId);
        $file = $request->file('file');

        if ($file) {
            $imageWebp = new ImageWebpService($file);
            $imageWebp->convert(EnumImageQuality::HD);
            $imageWebp->save($p, EnumTypeMedia::PHOTO_PUBLICATION);
//            $p->addMediaFromStream($imageWebp->first()->stream())
//                ->usingFileName(EnumTypeMedia::PHOTO_PUBLICATION->value . '.webp')
//                ->toMediaCollection(EnumTypeMedia::PHOTO_PUBLICATION->value);
        }

        return success(data: new PublicationResource($p));
    }

    public function deleteAllImages(int $publicationId)
    {
        $p = Publication::find($publicationId);
        $p->clearMediaCollection(EnumTypeMedia::PHOTO_PUBLICATION->value);

        return success(data: new PublicationResource($p));
    }

    public function addImgType(int $publicationTypeId, Request $request)
    {
        $p = PublicationType::find($publicationTypeId);

        $file = $request->file('file');

        if ($file) {
            $imageWebp = new ImageWebpService($file);
            $imageWebp->convert(EnumImageQuality::HD);
            $p->clearMediaCollection(EnumTypeMedia::PHOTO_PUBLICATION->value);
            $imageWebp->save($p, EnumTypeMedia::PHOTO_PUBLICATION);
//            $p->addMediaFromStream($imageWebp->first()->stream())
//                ->usingFileName(EnumTypeMedia::PHOTO_PUBLICATION->value . '.webp')
//                ->toMediaCollection(EnumTypeMedia::PHOTO_PUBLICATION->value);
        }


        return success(data: new PublicationTypeResource($p));
    }

    public function deleteAllImagesType(int $publicationTypeId)
    {
        $p = PublicationType::find($publicationTypeId);
        $p->clearMediaCollection(EnumTypeMedia::PHOTO_PUBLICATION->value);

        return success(data: new PublicationTypeResource($p));
    }

}
