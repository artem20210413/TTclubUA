<?php

namespace App\Http\Resources;

use App\Enum\EnumTypeMedia;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinanceWithUserResource extends JsonResource
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
            'user' => new UserResource($this->user)
        ];
    }
}
