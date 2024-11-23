<?php

namespace App\Services\Image;

use App\Enum\EnumImageQuality;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;


class ImageWebpService
{

    /** @var array|Image[] */
    private array $images;

    /** @param array|UploadedFile|UploadedFile[] ...$images */
    public function __construct(array|UploadedFile ...$images)
    {
        foreach ($images as $image) {
            $this->images[] = \Intervention\Image\Facades\Image::make($image)->encode('webp', 90);
        }
    }

    /** @return array|Image[] */
    public function convert(EnumImageQuality $enumImageQuality): array
    {
        foreach ($this->images as &$image) {
            $image->resize($enumImageQuality->getPx(), null, function ($constraint) {
                $constraint->aspectRatio(); // Сохраняем пропорции
                $constraint->upsize();      // Не увеличиваем изображение, если оно меньше
            });
        }

        return $this->images;
    }

    /**
     * @return array|Image[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @return Image
     */
    public function first(): Image
    {
        return $this->images[0];
    }
}
