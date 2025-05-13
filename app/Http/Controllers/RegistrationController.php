<?php

namespace App\Http\Controllers;

use App\Eloquent\RegistrationEloquent;
use App\Eloquent\UserEloquent;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\Car\CarResource;
use App\Http\Resources\User\UserRegistrationResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserWithCarsResource;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;


class RegistrationController extends Controller
{
    public function list(Request $request)
    {
        try {
            $r = Registration::query()->where('active', true)
                ->orderBy('created_at', 'desc')->paginate($request->perPage);

            return success(data: UserRegistrationResource::collection($r));
        } catch (ApiException $e) {
            return error($e);
        }
    }
    public function validator(Registration $registration)
    {
        try {
            $errors = RegistrationEloquent::validator($registration);

            if (!empty($errors)) {
                return response()->json([
                    'status' => 'error',
                    'messages' => $errors,
                ], 422);
            }

            return response()->json(['status' => 'ok']);

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function approve(Registration $registration)
    {
        try {

            if ($registration->approve) {
                throw new ApiException('Користувач вже створено.', 0, 401);
            }

            //TODO save User
            $registration->active = false;
            $registration->approve = true;
            $registration->save();

            return success(data: new UserRegistrationResource($registration));
        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function changeActive(Registration $registration)
    {
        try {
            if ($registration->approve) {
                throw new ApiException('Користувач вже створено.', 0, 401);
            }

            $registration->active = !$registration->active;
            $registration->save();

            return success(data: new UserRegistrationResource($registration));
        } catch (ApiException $e) {
            return error($e);
        }
    }


}
