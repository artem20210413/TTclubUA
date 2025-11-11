<?php

namespace App\Http\Controllers;

use App\Eloquent\UserEloquent;
use App\Enum\EnumTelegramChats;
use App\Exports\UserExport;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\Car\CarResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserWithCarsResource;
use App\Models\User;
use App\Services\Telegram\TelegramBot;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class UserController extends Controller
{

    public function user(Request $request)
    {
        return success(data: ['user' => new UserWithCarsResource($request->user())]);
    }

    public function getUser(User $user, Request $request)
    {
        return success(data: new UserWithCarsResource($user));
    }


    public function search(Request $request)
    {
        $search = str_replace(' ', '%', trim($request->search ?? ''));
        $query = User::query();
        $query = UserEloquent::search($query, $search);
        $query->orderBy('created_at', 'desc');

        return success(data: UserWithCarsResource::collection($query->paginate(15)));
    }

    public function all(Request $request)
    {
        return success(data: ['users' => UserResource::collection(User::all())]);
    }

    public function myCars(Request $request)
    {
        /** @var User $user */
        $user = $request->user();


        return success(data: CarResource::collection($user->cars));
    }


    public function update(UpdateUserRequest $request)
    {
        try {
            /** @var User $user */
            $user = $request->user();

            $user->updateCustom($request);

            return success(message: 'Користувача успішно оновлено', data: ['user' => new UserResource($user->refresh())]);

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function updateById(int $id, UpdateUserRequest $request)
    {
        try {
            /** @var User $user */
            $user = User::findOrFail($id);

            $user->updateCustom($request);

            return success(message: 'Користувача успішно оновлено', data: ['user' => new UserResource($user)]);

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function userChangeActive(User $user, UpdateUserRequest $request)
    {
        try {
            if ($request->user()->id === $user->id) throw new ApiException('Власний статус міняти заборонено', 1, 403);

            $user->active = !$user->active;
            $user->save();

            return success(message: 'Користувача успішно оновлено', data: ['user' => new UserResource($user)]);

        } catch (ApiException $e) {
            return error($e);
        }
    }

    public function export()
    {
        $q = User::query()->with('cars');
        $users = $q->get();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'Empty'], 404);
        }

        $fileName = 'users_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $relativePath = 'exports/' . $fileName; // относительно storage/app
        Excel::store(new UserExport($users), $relativePath);

        $absolutePath = storage_path('app/private/' . $relativePath);
//
        $bot = new TelegramBot(EnumTelegramChats::NOTIFICATION);
        $bot->sendDocumentWithCaption($absolutePath, 'Ось усі користувачі 🧾');

        if (file_exists($absolutePath)) {
            unlink($absolutePath);
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }


}
