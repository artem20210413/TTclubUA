<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $this */
//        return parent::toArray($request);
        $birthDate = $this->birth_date ? Carbon::parse($this->birth_date) : null;
        $clubEntryDate = $this->club_entry_date ? Carbon::parse($this->club_entry_date) : null;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'telegram_nickname' => $this->telegram_nickname,
            'instagram_nickname' => $this->instagram_nickname,
            'birth_date' => $birthDate?->format('d-m-Y'),
            'club_entry_date' => $clubEntryDate?->format('d-m-Y'),
            'occupation_description' => $this->occupation_description,
            'email' => $this->email,
            'phone' => $this->phone,
            'active' => (bool)$this->active,
            'updated_at' => $this->created_at->diffForHumans(),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
