<?php

use App\Http\Controllers\Api\ApiException;
use Illuminate\Database\Eloquent\Collection;

if (!function_exists('success')) {
    function success(?string $massage = null, array|Collection|ArrayAccess $data = [], int $status = 200): Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => $massage, 'data' => $data], $status);
    }
}

if (!function_exists('error')) {
    function error(ApiException $e): Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $e->getMessage(),
            'error' => $e->getCode()
        ], method_exists($e, 'getStatus') ? $e->getStatus() : 400);

    }
}
