<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserSmallResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CostsWithUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'owner' => new UserSmallResource($this->owner)
        ];
    }
}
