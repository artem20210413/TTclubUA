<?php

namespace App\Models;

use App\Events\Trigger\Registration\TriggerRegistrationCreatedEvent;
use App\Http\Requests\RegiserFormRequestOLD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class User
 *
 * @property int $id
 * @property string name
 * @property string phone
 * @property string ip
 * @property string password
 * @property string json
 * @property bool active
 * @property bool approve
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Registration extends Model implements HasMedia
{
    use HasProfilePhoto;
    use InteractsWithMedia;


    protected $dispatchesEvents = [
//        'created' => TriggerRegistrationCreatedEvent::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $registration) {
            $registration->ip = Request::ip(); // <-- здесь автоматически берём IP при создании
        });
    }

    public function generationJsomOld(\Illuminate\Http\Request $request): void
    {
        $json = $request->all();
        unset($json['_token'], $json['password'], $json['confirm_password'], $json['password_confirmation']);

        $json['cities_model'] = City::query()->whereIn('id', $json['cities'] ?? [])->get()->toArray();
        foreach ($json['cars'] as $key => &$car) {
            $car['model'] = CarModel::query()->select(['id', 'name'])->where('id', $car['model_id'])->first()->toArray();
            $car['gene'] = CarGene::query()->select(['id', 'name'])->where('id', $car['gene_id'])->first()->toArray();
            $car['color'] = Color::query()->where('id', $car['color_id'])->first()->toArray();
        }

        $this->json = json_encode($json);
    }

    public function generationJsom(\Illuminate\Http\Request $request): void
    {
        $json = $request->all();
        unset($json['_token'], $json['password'], $json['confirm_password'], $json['password_confirmation']);

        $json['cities_model'] = City::query()->whereIn('id', [$json['city_id']])->get()->toArray();

        $car = $json['car'] ?? null;
        if (isset($car)) {
            $car['model'] = CarModel::query()->select(['id', 'name'])->where('id', $car['model_id'])->first()->toArray();
            $car['gene'] = CarGene::query()->select(['id', 'name'])->where('id', $car['gene_id'])->first()->toArray();
            $car['color'] = Color::query()->where('id', $car['color_id'])->first()->toArray();
            $json['car'] = $car;
        }

        $this->json = json_encode($json);
    }

    public function getJson()
    {
        return json_decode($this->json);
    }

    public function setPhone(string $phone): void
    {
        $this->phone = formatPhoneNumber($phone);
    }

    public function setPassword(string $password): void
    {
        $this->password = Hash::make($password);
    }

}
