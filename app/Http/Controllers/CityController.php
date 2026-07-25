<?php

namespace App\Http\Controllers;

use App\Http\Resources\CityResource;
use App\Http\Resources\User\UserResource;
use App\Models\City;
use Illuminate\Pagination\LengthAwarePaginator;

class CityController extends Controller
{
    public function all()
    {
        return success(data: ['cities' => CityResource::collection(City::orderBy('name')->get())]);
    }

    public function mapAggregated()
    {
        $cities = City::withUsersCount()
            ->hasUsers()
            ->orderBy('name')
            ->get();

        // Each city in this subset has exactly one user, so eager loading it here
        // (no limit needed) is a single extra query regardless of how many such cities exist.
        $cities->where('users_count', 1)->load('users.media');

        return success(data: ['cities' => CityResource::collection($cities)]);
    }

    public function usersByCity(City $city)
    {
        return success(data: UserResource::collection($city->users()->paginate(15)));
    }

    public function usersByCityMissing()
    {
        $perPage = (int) (request()->query('per_page', 15));

        return success(data: UserResource::collection(new LengthAwarePaginator([], 0, $perPage)));
    }
}
