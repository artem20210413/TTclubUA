<?php

namespace App\Http\Controllers\Car;

use App\Eloquent\CarEloquent;
use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Car\AddCollectionsCarRequest;
use App\Http\Requests\Car\CreateCarRequest;
use App\Http\Requests\Car\UpdateCarRequest;
use App\Http\Resources\Car\CarResource;
use App\Http\Resources\Car\CarWithUserResource;
use App\Http\Resources\Car\ColorResource;
use App\Http\Resources\Car\GenesResource;
use App\Http\Resources\Car\ModelResource;
use App\Models\Car;
use App\Models\CarGene;
use App\Models\CarModel;
use App\Models\Color;
use App\Models\User;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\Request;


class CarController extends Controller
{

    public function create(CreateCarRequest $request)
    {
        $car = new Car();
        $car->user_id = $request->user_id;
        $car->gene_id = $request->gene_id;
        $car->model_id = $request->model_id;
        $car->color_id = $request->color_id;
        $car->name = $request->name;
        $car->vin_code = $request->vin_code;
        $car->license_plate = $request->license_plate;
        $car->personalized_license_plate = $request->personalized_license_plate;
        $car->save();

        return success('Авто створено', new CarResource($car->fresh()));
    }

    public function all()
    {
        return success(data: CarResource::collection(Car::all()));
    }

    public function search(string $search, Request $request)
    {
        $q = Car::query();
        $cars = CarEloquent::search($q, $search)->paginate($request->perPage ?? 10);

        return success(data: CarWithUserResource::collection($cars));
    }

    public function find(int $id)
    {
        try {
            $car = Car::findOrFail($id);

            return success(data: new CarResource($car));

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function myCars(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return success(data: CarResource::collection($user->cars ?? []));
    }

    public function update(int $id, UpdateCarRequest $request)
    {
        try {
//            /** @var User $user */
//            $user = $request->user();
//TODO check privet car


            $car = Car::findOrFail($id);
            $car->updateCustom($request);

            return success(massage: 'Авто успішно оновлено', data: new CarResource($car->refresh()));

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function addCollections(int $id, AddCollectionsCarRequest $request)
    {
        try {
            $car = Car::find($id);

            $file = $request->file('file'); // Массив изображений, переданных через запрос
            if (!$file) throw new ApiException('Фото відсутні.', 0, 400);

            if ($car->hasMedia(EnumTypeMedia::PHOTO_COLLECTION->value))
                $car->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value)->each->delete();

            $imageWebp = new ImageWebpService($file);
            $images = $imageWebp->convert(EnumImageQuality::HD);
            $imageWebp->save($car, EnumTypeMedia::PHOTO_COLLECTION, 'collection_image_' . time());

            return success(massage: 'Колекція успішно оновлено.', data: new CarResource($car->refresh()));

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function deleteCollections(int $id, int $mediaId, Request $request)
    {
        try {
            $car = Car::findOrFail($id);
            $media = $car->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value)->firstWhere('id', $mediaId);
            if (!$media) throw new ApiException('Фото не знайдено.', 0, 400);
            $media->delete();

            return success(massage: 'Колекція успішно оновлено.', data: new CarResource($car->refresh()));

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function genes()
    {
        return success(data: GenesResource::collection(CarGene::all()));
    }

    public function models()
    {
        return success(data: ModelResource::collection(CarModel::all()));
    }

    public function colors()
    {
        return success(data: ColorResource::collection(Color::all()));
    }

    public function changeActive(Car $car)
    {
        try {
            $car->active = !$car->active;
            $car->save();

            return success(massage: 'Авто успішно оновлено', data: new CarResource($car));

        } catch (ApiException $e) {
            return error($e);
        }
    }
}
