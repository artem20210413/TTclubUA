<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $costsPaginator = $this['costs'];
        $costsMeta = $costsPaginator->toArray();
        unset($costsMeta['data']);

        return [
            'season_start' => $this['season_start']?->format('Y-m-d'),
            'season_end' => $this['season_end']?->format('Y-m-d'),
            'spent_total' => number_format($this['spent_total'], 2, '.', ''),
            'collected_total' => number_format($this['collected_total'], 2, '.', ''),
            'opening_balance' => number_format($this['opening_balance'], 2, '.', ''),
            'closing_balance' => number_format($this['closing_balance'], 2, '.', ''),
            'costs' => array_merge($costsMeta, [
                'data' => CostsWithUserResource::collection($costsPaginator),
            ]),
        ];
    }
}
