<?php

namespace App\Services\Mention;

use App\Models\Mention;

class MentionLocationService
{
    /**
     * Радіус (у метрах), в межах якого точки вважаються "дуже близькими"
     * і об'єднуються в одну — з підрахунком кількості "фа-фа" в ній.
     */
    private const CLUSTER_RADIUS_METERS = 50;

    /**
     * Приблизна кількість метрів в одному градусі широти.
     */
    private const METERS_PER_DEGREE = 111_320;

    /**
     * @return array<int, array{latitude: float, longitude: float, count: int}>
     */
    public static function getClusteredLocations(?int $radiusMeters = null): array
    {
        $radiusMeters ??= self::CLUSTER_RADIUS_METERS;

        $points = Mention::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['latitude', 'longitude']);

        if ($points->isEmpty()) {
            return [];
        }

        $cellSizeDegrees = $radiusMeters / self::METERS_PER_DEGREE;

        $clusters = [];

        foreach ($points as $point) {
            $cellKey = floor($point->latitude / $cellSizeDegrees).':'.floor($point->longitude / $cellSizeDegrees);

            if (! isset($clusters[$cellKey])) {
                $clusters[$cellKey] = [
                    'latitude_sum' => 0.0,
                    'longitude_sum' => 0.0,
                    'count' => 0,
                ];
            }

            $clusters[$cellKey]['latitude_sum'] += (float) $point->latitude;
            $clusters[$cellKey]['longitude_sum'] += (float) $point->longitude;
            $clusters[$cellKey]['count']++;
        }

        return array_values(array_map(static fn (array $cluster) => [
            'latitude' => round($cluster['latitude_sum'] / $cluster['count'], 7),
            'longitude' => round($cluster['longitude_sum'] / $cluster['count'], 7),
            'count' => $cluster['count'],
        ], $clusters));
    }
}
