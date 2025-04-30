<?php

namespace App\Http\Resources\User;

use App\Enum\EnumTypeMedia;
use App\Http\Resources\CityResource;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Registration $this */

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'json' => json_decode($this->json),
        ];
    }
}
