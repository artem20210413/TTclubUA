<?php

namespace App\Http\Resources\Car;

use App\Enum\EnumTypeMedia;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{

    protected $_isSale;

    // Перевизначаємо конструктор
    public function __construct($resource, $isSale = false)
    {
        parent::__construct($resource);
        $this->_isSale = $isSale;
    }

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
            'user_id' => $this->user_id,
            'vin_code' => $this->vin_code,
            'license_plate' => $this->license_plate,
            'personalized_license_plate' => $this->personalized_license_plate,
            'general_license_plate' => $this->getGeneralLicensePlate(),
            'gene' => new GenesResource($this->gene),
            'model' => new ModelResource($this->model),
            'color' => new ColorResource($this->color),
            'imageUrls' => $imageUrls,
            'active' => $this->active,
            'is_sale' => $this->_isSale,
            'updated_at' => $this->updated_at?->diffForHumans(),
            'created_at' => $this->created_at?->diffForHumans(),
        ];
    }
}
