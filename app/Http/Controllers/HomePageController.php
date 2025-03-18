<?php

namespace App\Http\Controllers;

use App\Eloquent\CarEloquent;
use App\Eloquent\UserEloquent;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\ChangePasswordByUserRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserWithCarsResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class HomePageController extends Controller
{
    public function homepageData()
    {

        $birthdaysNextWeek = UserEloquent::getBirthdayPeople(7);
        $newMembersLastNDays = UserEloquent::getNewMembersLastNDays(30);//30
        $totalMembers = UserEloquent::countUsersWithCars();
        $totalCars = CarEloquent::countCarsWithUsers();
        return success(data: [
            'birthdays_next_week' => UserWithCarsResource::collection($birthdaysNextWeek),
            'new_members_this_month' => UserWithCarsResource::collection($newMembersLastNDays),
            'total_members' => $totalMembers, // общее количество участников
            'total_cars' => $totalCars // общее количество машин с участниками
        ]);
    }

}
