<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Prize\StorePrizeRequest;
use App\Http\Requests\Prize\UpdatePrizeRequest;
use App\Http\Resources\PrizeResource;
use App\Models\Draw;
use App\Models\Prize;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PrizeController extends Controller
{
    public function index(Draw $draw): AnonymousResourceCollection
    {
        return PrizeResource::collection($draw->prizes);
    }

    public function store(StorePrizeRequest $request, Draw $draw): PrizeResource
    {
        $prize = $draw->prizes()->create($request->validated());

        return new PrizeResource($prize);
    }

    public function update(UpdatePrizeRequest $request, Draw $draw, Prize $prize): PrizeResource
    {
        $prize->update($request->validated());

        return new PrizeResource($prize);
    }

    public function destroy(Draw $draw, Prize $prize): Response
    {
        $prize->delete();

        return response()->noContent();
    }
}
