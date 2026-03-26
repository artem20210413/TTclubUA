<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use App\Http\Resources\User\UserResource;
use App\Models\ExternalCar;
use App\Models\Prize;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExternalCarResource extends JsonResource
{
    public function toArray(Request $request): array
    {


        /** @var ExternalCar $this */
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->raw_data['autoData']['description'] ?? null,
            'price_usd' => $this->price_usd,
            'city_name' => $this->city_name,
            'regionName' => $this->raw_data['stateData']['regionName'] ?? null,
            'mark_name' => $this->mark_name,
            'model_name' => $this->model_name,
            'sub_category' => $this->sub_category,
            'race' => $this->raw_data['autoData']['race'] ?? null,
            'fuelName' => $this->raw_data['autoData']['fuelName'] ?? null,
            'driveName' => $this->raw_data['autoData']['driveName'] ?? null,
            'equipmentName' => $this->raw_data['autoData']['equipmentName'] ?? null,
            'generationName' => $this->raw_data['autoData']['generationName'] ?? null,
            'gearboxName' => $this->raw_data['autoData']['gearboxName'] ?? null,
            'modificationName' => $this->raw_data['autoData']['modificationName'] ?? null,
            'year' => $this->year,
            'linkToView' => isset($this->raw_data['linkToView']) ? "https://auto.ria.com" . $this->raw_data['linkToView'] : null,
            'color' => $this->raw_data['color'] ?? [],
            'images' => $this->getAllPhotos(),
            'user' => new UserResource($this->user),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
