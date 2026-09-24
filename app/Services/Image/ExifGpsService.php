<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;

class ExifGpsService
{
    /**
     * Витягує координати GPS з EXIF-метаданих файлу.
     * Не працює для HEIC — розширення ext-exif підтримує лише JPEG/TIFF,
     * тому для HEIC використовуй extractFromString() на JPG, отриманому після конвертації.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public static function extract(UploadedFile $file): ?array
    {
        $path = $file->getRealPath();

        if (! $path) {
            return null;
        }

        return self::read($path);
    }

    /**
     * Витягує координати GPS із бінарного вмісту зображення (наприклад, JPG,
     * отриманий у пам'яті після конвертації з HEIC).
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public static function extractFromString(string $binary): ?array
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $binary);
        rewind($stream);

        $result = self::read($stream);

        fclose($stream);

        return $result;
    }

    /**
     * @param  resource|string  $fileOrStream
     * @return array{latitude: float, longitude: float}|null
     */
    private static function read($fileOrStream): ?array
    {
        if (! function_exists('exif_read_data')) {
            return null;
        }

        $exif = @exif_read_data($fileOrStream, 'GPS', true);

        if (! $exif || empty($exif['GPS']['GPSLatitude']) || empty($exif['GPS']['GPSLongitude'])) {
            return null;
        }

        $latitude = self::toDecimal(
            $exif['GPS']['GPSLatitude'],
            $exif['GPS']['GPSLatitudeRef'] ?? 'N'
        );

        $longitude = self::toDecimal(
            $exif['GPS']['GPSLongitude'],
            $exif['GPS']['GPSLongitudeRef'] ?? 'E'
        );

        if ($latitude === null || $longitude === null) {
            return null;
        }

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    private static function toDecimal(array $coordinate, string $hemisphere): ?float
    {
        if (count($coordinate) < 3) {
            return null;
        }

        $degrees = self::partToFloat($coordinate[0]);
        $minutes = self::partToFloat($coordinate[1]);
        $seconds = self::partToFloat($coordinate[2]);

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if (in_array(strtoupper($hemisphere), ['S', 'W'], true)) {
            $decimal *= -1;
        }

        return round($decimal, 7);
    }

    private static function partToFloat(string $part): float
    {
        if (str_contains($part, '/')) {
            [$numerator, $denominator] = array_map('floatval', explode('/', $part, 2));

            return $denominator ? $numerator / $denominator : 0.0;
        }

        return (float) $part;
    }
}
