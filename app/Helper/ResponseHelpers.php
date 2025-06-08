<?php

use App\Http\Controllers\Api\ApiException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

if (!function_exists('success')) {
//    function success(?string $message = null, array|Collection|ArrayAccess $data = [], int $status = 200): Illuminate\Http\JsonResponse
//    {
//        return response()->json(['message' => $message, 'data' => $data], $status);
//    }
    function success(?string $message = null, array|Collection|ArrayAccess|AnonymousResourceCollection $data = [], int $status = 200): Illuminate\Http\JsonResponse
    {
        $res = [
            'message' => $message,
            'data' => $data,
        ];

        if ($data instanceof AnonymousResourceCollection && $data->resource instanceof \Illuminate\Contracts\Pagination\Paginator) {
            $pagination = $data->resource->toArray();
            unset($pagination['data']); // убираем данные из meta
            $res['meta'] = $pagination;
        }

        return response()->json($res, $status);
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
