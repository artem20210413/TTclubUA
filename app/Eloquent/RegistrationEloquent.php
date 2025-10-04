<?php

namespace App\Eloquent;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTelegramChats;
use App\Enum\EnumTypeMedia;
use App\Http\Requests\MentionRequest;
use App\Models\Car;
use App\Models\Mention;
use App\Models\Registration;
use App\Models\User;
use App\Services\Image\ImageWebpService;
use App\Services\Validator\Elements\CarsValidator;
use App\Services\Validator\Elements\UserValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;

class RegistrationEloquent
{
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

    public static function delete(Registration $registration): ?bool
    {
        if ($registration->hasMedia(EnumTypeMedia::PROFILE_PICTURE->value)) {
            $registration->getMedia(EnumTypeMedia::PROFILE_PICTURE->value)->each->delete();
        }
        if ($registration->hasMedia(EnumTypeMedia::PHOTO_COLLECTION->value)) {
            $registration->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value)->each->delete();
        }
        return $registration->delete();
    }

    public static function validator(Registration $registration)
    {
        $data = json_decode($registration->json, true);

        $validator = new UserValidator();
        $validator
            ->setNext(new CarsValidator());

        return $validator->validate($data);
    }

    public static function create(Registration $registration)
    {
        $imageUrls = $registration->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value);
        $profileImage = $registration->getMedia(EnumTypeMedia::PROFILE_PICTURE->value)->first();
        $data = json_decode($registration->json, true);
        $car = $data['car'] ?? $data['cars'][0] ?? null;

        $user = new User();
        $user->name = $data['name'];
        $user->setPhone($data['phone']);
        $user->email = $user->phone . '@email';
        $user->birth_date = $data['birth_date'] ? Carbon::create($data['birth_date'])->format('Y-m-d') : null;
        $user->password = $registration->password;
        $user->telegram_nickname = isset($data['telegram_nickname']) ? str_replace('@', '', $data['telegram_nickname']) : null;
        $user->why_tt = $data['why_tt'] ?? null;
        $user->mail_address = $data['mail_address'] ?? null;
        $user->instagram_nickname = $data['instagram_nickname'];
        $user->occupation_description = $data['occupation_description'];
        $user->is_tt = isset($data['is_tt']) ? true : false;

        $user->save();
        $user->cities()->sync($data['cities'] ?? []);
        $user = $user->refresh();

        if ($profileImage) {
            $user->addMedia($profileImage->getPath())
                ->preservingOriginal()
                ->toMediaCollection(EnumTypeMedia::PROFILE_PICTURE->value);

            $profileImage->delete();
        }

        if ($car) {
            $img = $imageUrls[0] ?? null;
            $car = new Car();
            $car->user_id = $user->id;
            $car->gene_id = $carData['gene']['id'] ?? null;
            $car->color_id = $carData['color']['id'] ?? null;
            $car->model_id = $carData['model']['id'] ?? null;
            $car->vin_code = $carData['vin_code'] ?? null;
            $car->license_plate = formatNormalizePlateNumber($carData['license_plate'] ?? null);
            $car->personalized_license_plate = formatNormalizePlateNumber($carData['personalized_license_plate'] ?? null);

            $car->save();
            if ($img) {
                $car->addMedia($img->getPath())
                    ->preservingOriginal()
                    ->toMediaCollection(EnumTypeMedia::PHOTO_COLLECTION->value);

                $img->delete();
            }
        }
//        foreach ($data['cars'] ?? [] as $key => $carData) {
//
//            $img = $imageUrls[$key] ?? null;
//            $car = new Car();
//            $car->user_id = $user->id;
//            $car->gene_id = $carData['gene']['id'] ?? null;
//            $car->color_id = $carData['color']['id'] ?? null;
//            $car->model_id = $carData['model']['id'] ?? null;
//            $car->vin_code = $carData['vin_code'] ?? null;
//            $car->license_plate = formatNormalizePlateNumber($carData['license_plate'] ?? null);
//            $car->personalized_license_plate = formatNormalizePlateNumber($carData['personalized_license_plate'] ?? null);
//
//            $car->save();
//            if ($img) {
//                $car->addMedia($img->getPath())
//                    ->preservingOriginal()
//                    ->toMediaCollection(EnumTypeMedia::PHOTO_COLLECTION->value);
//
//                $img->delete();
//            }
//        }

        return $user;
    }


}
