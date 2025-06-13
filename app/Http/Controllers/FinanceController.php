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

        $f = Finance::query()->where('user_id', $user->id)->paginate($request->pa);
        return success(data: FinanceWithUserResource::collection($f));
    }

    public function statistics(User $user, Request $request)
    {
        return success(data: [
            'all_sum' => Finance::query()->where('user_id', $user->id)->sum('amount'),
//            'last_year' => Finance::query()->where('user_id', $user->id)->where('created_at', '>=', now()->startOfYear())->sum('amount'),
            'last_year' => Finance::query()->where('user_id', $user->id)->where('created_at', '>=', now()->subDays(365))->sum('amount'),
//            'last_month' => Finance::query()->where('user_id', $user->id)->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'last_month' => Finance::query()->where('user_id', $user->id)->where('created_at', '>=', now()->subDays(30))->sum('amount'),
            'total_payments_count' => Finance::where('user_id', $user->id)->count(), // сколько всего платежей сделал пользователь
            'average_payment' => round((float) Finance::where('user_id', $user->id)->avg('amount'), 2),
            'largest_payment' => Finance::where('user_id', $user->id)->max('amount'), // наибольший платёж
            'smallest_payment' => Finance::where('user_id', $user->id)->min('amount'), // наименьший платёж
            'last_payment_date' => Finance::where('user_id', $user->id)->latest()->value('created_at'), // дата последнего платежа
            'first_payment_date' => Finance::where('user_id', $user->id)->oldest()->value('created_at'),// когда был сделан первый платёж

        ]);
    }

}
