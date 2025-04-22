<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class User
 *
 * @property int $id
 * @property string name
 * @property string phone
 * @property string license_plate
 * @property string short_name_car
 * @property string password
 * @property string json
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Registration extends Model implements HasMedia
{
    use HasProfilePhoto;
    use InteractsWithMedia;

    public function setJson(array $data): void
    {
        $this->json = json_encode($data);
    }

    public function getJson()
    {
        return json_decode($this->json);
    }

}
