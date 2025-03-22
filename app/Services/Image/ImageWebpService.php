<?php

namespace App\Services\Image;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Maestroerror\HeicToJpg;
use Spatie\MediaLibrary\HasMedia;


class ImageWebpService
{

    /** @var array|Image[] */
    private array $images;

    /** @param array|UploadedFile|UploadedFile[] ...$images */
    public function __construct(array|UploadedFile ...$images)
    {
        foreach ($images as $image) {

            if (HeicToJpg::isHeic($image))
                $image = HeicToJpg::convert($image)->get();

            $this->images[] = \Intervention\Image\Facades\Image::make($image)->orientate()->encode('webp', 90);
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

    public function save(HasMedia $p, EnumTypeMedia $typeMedia, ?string $name = null): void
    {
        $name = $name ?? $typeMedia->value;

        foreach ($this->getImages() as $image) {
            $p->addMediaFromStream($image->stream())
                ->usingFileName($name . '.webp')
                ->toMediaCollection($typeMedia->value);
        }

    }
}
