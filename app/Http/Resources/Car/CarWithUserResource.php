<?php

namespace App\Http\Resources\Car;

use App\Enum\EnumTypeMedia;
use App\Http\Controllers\UserController;
use App\Http\Resources\User\UserResource;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarWithUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Car $this */

        $imageUrls = $this->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value)->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
            ];
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'vin_code' => $this->vin_code,
            'license_plate' => $this->license_plate,
            'personalized_license_plate' => $this->personalized_license_plate,
            'general_license_plate' => $this->getGeneralLicensePlate(),
            'gene' => new GenesResource($this->gene),
            'model' => new ModelResource($this->model),
            'user' => new UserResource($this->user),
            'imageUrls' => $imageUrls,
            'updated_at' => $this->updated_at->diffForHumans(),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
