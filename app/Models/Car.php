<?php

namespace App\Models;

use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\Car\UpdateCarRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Car
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $gene_id
 * @property int $model_id
 * @property int $color_id
 * @property string|null $name
 * @property string|null $vin_code
 * @property string $license_plate
 * @property string|null $personalized_license_plate
 * @property string|null $photo_path
 * @property bool|null $active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property \App\Models\User $user
 * @property \App\Models\CarGene $gene
 * @property \App\Models\CarModel $model
 * @property \App\Models\Color $color
 */
class Car extends Model implements HasMedia
{
    use HasFactory;

    use HasProfilePhoto;
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'gene_id',
        'model_id',
        'color_id',
        'name',
        'license_plate',
        'personalized_license_plate',
        'photo_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gene(): BelongsTo
    {
        return $this->belongsTo(CarGene::class, 'gene_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'model_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function updateCustom(UpdateCarRequest $request): Car
    {
        $this->user_id = $request->input('user_id', $this->user_id); // Аналогично для других полей
        $this->gene_id = $request->input('gene_id', $this->gene_id); // Аналогично для других полей
        $this->model_id = $request->input('model_id', $this->model_id); // Аналогично для других полей
        $this->color_id = $request->input('color_id', $this->color_id); // Аналогично для других полей
        $this->name = $request->input('name', $this->name); // Если поле пустое, сохраняем старое значение
        $this->vin_code = $request->input('vin_code', $this->vin_code); // Аналогично для других полей
        $this->license_plate = $request->input('license_plate', $this->license_plate); // Аналогично для других полей
        $this->personalized_license_plate = $request->input('personalized_license_plate', $this->personalized_license_plate); // Аналогично для других полей

        $this->save();

        return $this;
    }

    /**
     * @param int $id
     * @return Car
     * @throws ApiException
     */
    static function findOrFail(int $id): Car
    {
        $car = Car::find($id);

        if (!$car)
            throw  new ApiException('Авто не існує', 0, 400);


        return $car;
    }

    public function getGeneralLicensePlate(): ?string
    {
        return $this->personalized_license_plate ?? $this->license_plate;
    }

    public function getGeneralShortInfo(): string
    {
        $gen = $this?->gene->name ?? '-';
        $model = $this?->model->name ?? '-';
        $l = $this->getGeneralLicensePlate() ?? '-';
        return "$model $gen ($l)";
    }

}
