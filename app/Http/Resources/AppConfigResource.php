<?php

namespace App\Http\Resources;

use App\Enum\UpdateStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppConfigResource extends JsonResource
{
    private UpdateStatusEnum $updateStatus;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  UpdateStatusEnum $updateStatus
     * @return void
     */
    public function __construct($resource, UpdateStatusEnum $updateStatus)
    {
        parent::__construct($resource);
        $this->updateStatus = $updateStatus;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Для статуса UP_TO_DATE возвращаем только сам статус
        if ($this->updateStatus === UpdateStatusEnum::UP_TO_DATE) {
            return [
                'update_status' => $this->updateStatus->value,
            ];
        }

        // Для остальных статусов (FORCE_UPDATE, UPDATE_AVAILABLE) возвращаем полные данные
        return [
            'update_status' => $this->updateStatus->value,
            'latest_version' => $this->resource->latest_version,
            'store_url' => $this->resource->store_url,
            'release_notes' => $this->resource->release_notes,
        ];
    }
}
