<?php

namespace App\Eloquent;

use App\Models\Car;
use App\Models\RemoteCar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RemoteCarEloquent
{

    public static function create(Car $car)
    {
        $m = new RemoteCar();
        $m->user_id = Auth::id();
        $m->data = json_encode($car->toArray());
        $m->save();
    }
}
