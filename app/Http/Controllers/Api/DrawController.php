<?php

namespace App\Http\Controllers\Api;

use App\Enum\DrawStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Draw\StoreDrawRequest;
use App\Http\Requests\Draw\UpdateDrawRequest;
use App\Http\Resources\DrawResource;
use App\Models\Draw;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DrawController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $draws = Draw::orderBy('created_at', 'desc')->paginate(15);

        return DrawResource::collection($draws);
    }

    public function show(Draw $draw): DrawResource
    {
        $draw->with(['participants.user', 'prizes']);

        return new DrawResource($draw);
    }

    public function store(StoreDrawRequest $request): DrawResource
    {
        $draw = Draw::create($request->validated());

        return new DrawResource($draw);
    }

    public function update(UpdateDrawRequest $request, Draw $draw): DrawResource
    {
        $draw->update($request->validated());

        return new DrawResource($draw);
    }

    public function deactivate(Draw $draw): DrawResource
    {
        $draw->update(['status' => DrawStatus::CANCELLED]);

        return new DrawResource($draw);
    }
}
