<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class User
 *
 * @property int $id
 * @property int $owner_id
 * @property int $car_id
 * @property string $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Mention extends Model implements HasMedia
{
    use HasProfilePhoto;
    use InteractsWithMedia;

    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    public function car()
    {
        return $this->belongsTo(\App\Models\Car::class, 'car_id');
    }

    /**
     * Получить владельца машины напрямую через модель Car.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function carOwnerUser(): HasOneThrough
    {
        // Мы проходим "через" модель Car, чтобы добраться до модели User.
        // Laravel будет искать 'car_id' в таблице 'mentions'
        // и 'user_id' в таблице 'cars'.
        return $this->hasOneThrough(
            User::class, // Модель, к которой мы хотим получить доступ
            Car::class,  // Промежуточная модель
            'id',     // Внешний ключ в таблице 'mentions' (связь с Car) -> mentions.car_id
            'id',     // Внешний ключ в таблице 'cars' (связь с User) -> cars.user_id
            'car_id',    // Локальный ключ в таблице 'mentions'
            'user_id'    // Локальный ключ в таблице 'cars'
        );
    }
}
