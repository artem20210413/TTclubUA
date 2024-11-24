<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\CityResource;
use App\Http\Resources\UserResource;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\Request;


class CityController extends Controller
{

    public function all()
    {
        return success(data: ['cities' => CityResource::collection(City::all())]);
    }


}
