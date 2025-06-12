<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\FinanceRequest;
use App\Http\Requests\User\ChangePasswordByUserRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\FinanceWithUserResource;
use App\Http\Resources\User\UserResource;
use App\Models\Finance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FinanceController extends Controller
{

    public function set(User $user, FinanceRequest $request)
    {
        $finance = new Finance();
        $finance->user_id = $user->id;
        $finance->amount = $request->amount;
        $finance->description = $request->description;
        $finance->save();

        return new FinanceWithUserResource($finance);
    }

    public function delete(Finance $finance)
    {
        $finance->delete();

        return success(message: 'Успішно видалено ');
    }

    public function list(User $user, Request $request)
    {
        $f = Finance::query()->where('user_id', $user->id)->get();
        return success(data: FinanceWithUserResource::collection($f));
    }

}
