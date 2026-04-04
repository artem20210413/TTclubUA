<?php

namespace App\Http\Controllers\Car;

use App\Eloquent\CarEloquent;
use App\Eloquent\RemoteCarEloquent;
use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Enum\EnumUserRoles;
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
        /** @var User $user */
        $user = User::find($request->user_id);
        $user->is_tt = true;
        $user->save();

        $user->addRoleEnum(EnumUserRoles::TTOWNER);

        $car = new Car();
        $car->user_id = $user->id;
        $car->gene_id = $request->gene_id;
        $car->model_id = $request->model_id;
        $car->color_id = $request->color_id;
        $car->name = $request->name;
        $car->vin_code = $request->vin_code;
        $car->license_plate = formatNormalizePlateNumber($request->license_plate);
        $car->personalized_license_plate = formatNormalizePlateNumber($request->personalized_license_plate);
        $car->save();

        return success('Авто створено', new CarResource($car->fresh()));
    }

    public function all()
    {
        $cars = Car::query();
        return success(data: CarResource::collection($cars->paginate(15)));
    }

    public function search(Request $request)
    {
        $search = trim((string)$request->input('search'));

        $q = Car::query();
        $q = CarEloquent::search($q, $search);

        $q->addSelect([
            'external_car_id' => function ($query) {
                $query->select('id')
                    ->from('external_cars')
                    ->whereColumn('external_cars.plate_number', 'cars.license_plate')
                    ->where('external_cars.is_active', 1)
                    ->where('external_cars.is_sold', 0)
                    ->limit(1); // Обов'язково, щоб не було помилки Cardinality violation
            }
        ]);
//        $q->addSelect([
//            'is_sale' => \App\Models\ExternalCar::selectRaw('1')
//                ->whereColumn('plate_number', 'cars.license_plate')
//                ->where('is_active', true)
//                ->where('is_sold', false)
//                ->limit(1)
//        ]);

        $geneIds = $request->input('gene_ids');
        $modelIds = $request->input('model_ids');
        $colorIds = $request->input('color_ids');
        $city = trim((string)$request->input('city'));

        if (!$search && !$geneIds && !$modelIds && !$colorIds && !$city) {
            $q = CarEloquent::onlyActiveUser($q);
        }

        if ($city)
            $q->whereHas('user.cities', function ($cityQuery) use ($city) {
                $cityQuery->where('cities.name', 'LIKE', "%{$city}%");
            });


        if ($geneIds) $q->whereIn('gene_id', explode(',', $geneIds));


        if ($modelIds) $q->whereIn('model_id', explode(',', $modelIds));


        if ($colorIds) $q->whereIn('color_id', explode(',', $colorIds));

        $q->orderBy('created_at', 'desc');
        $cars = $q->paginate($request->perPage ?? 15);

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

    public function delete(int $id)
    {
        try {
            $car = Car::findOrFail($id);

            $isDelete = $car->delete();
            RemoteCarEloquent::create($car);

            return success(data: ['isDelete' => $isDelete]);

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function deleteMyCar(Car $car)
    {
        try {
            if (!$car->isMine()) throw new ApiException('Авто не знайдено', 1, 404);

            $isDelete = $car->delete();
            RemoteCarEloquent::create($car);

            return success(data: ['isDelete' => $isDelete]);

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

            return success(message: 'Авто успішно оновлено', data: new CarResource($car->refresh()));

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

            return success(message: 'Колекція успішно оновлено.', data: new CarResource($car->refresh()));

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

            return success(message: 'Колекція успішно оновлено.', data: new CarResource($car->refresh()));

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

            return success(message: 'Авто успішно оновлено', data: new CarResource($car));

        } catch (ApiException $e) {
            return error($e);
        }
    }
}
