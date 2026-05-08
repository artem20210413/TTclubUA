<?php

namespace App\Http\Controllers;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Resources\EventResource;
use App\Http\Resources\GoodsResource;
use App\Models\Event;
use App\Models\FcmToken;
use App\Models\Goods;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // Отримуємо платформу з headers
        $platform = $request->header('X-Client-Platform');

        // Викликаємо метод моделі
        $fcmToken = FcmToken::updateToken(
            auth()?->id() ?? null,
            $request->token,
            $platform
        );

        return success(data: [
            'success' => true,
            'message' => 'Token updated successfully',
            'data' => $fcmToken
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'active' => 'required|boolean'
        ]);

        FcmToken::where('token', $request->token)
            ->where('user_id', auth()?->id() ?? null)
            ->update(['active' => $request->active]);

        return success(data: ['success' => true]);
    }

}
