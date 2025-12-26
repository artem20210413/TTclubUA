<?php

namespace App\Http\Controllers;

use App\Enum\UpdateStatusEnum;
use App\Http\Resources\AppConfigResource;
use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppConfigController extends Controller
{
    public function check(Request $request, string $platform)
    {
        $clientVersion = $request->header('X-App-Version');
        Log::info("AppConfigController", ['platform' => $platform, 'clientVersion' => $clientVersion, 'user' => Auth::user()?->toArray()]);

        // Если клиент не передал свою версию, ничего не делаем
        if (!$clientVersion) {
            return success(data: new AppConfigResource(null, UpdateStatusEnum::UP_TO_DATE));
//            return success(data: ['update_status' => UpdateStatusEnum::UP_TO_DATE->value]);
        }

        // Ищем активную конфигурацию для запрошенной платформы
        $config = AppConfig::where('platform', $platform)
            ->where('is_active', true)
            ->first();

        // Если конфига нет, считаем, что все в порядке
        if (!$config) {
            return success(data: new AppConfigResource(null, UpdateStatusEnum::UP_TO_DATE));
//            return success(data: ['update_status' => UpdateStatusEnum::UP_TO_DATE->value]);
        }

        // Сравниваем версии и определяем статус
        if (version_compare($clientVersion, $config->min_version, '<')) {
            $status = UpdateStatusEnum::FORCE_UPDATE;
        } elseif (version_compare($clientVersion, $config->latest_version, '<')) {
            $status = UpdateStatusEnum::UPDATE_AVAILABLE;
        } else {
            $status = UpdateStatusEnum::UP_TO_DATE;
        }

        // Возвращаем данные через ресурс
        return success(data: new AppConfigResource($config, $status));
    }
}
