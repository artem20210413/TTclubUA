<?php

namespace App\Http\Controllers;

use App\Http\Requests\CostsRequest;
use App\Http\Resources\CostsWithUserResource;
use App\Models\Costs;
use Illuminate\Http\Request;

class CostsController extends Controller
{
    public function set(CostsRequest $request)
    {

        $costs = new Costs;
        $costs->owner_id = $request->user()->id;
        $costs->amount = $request->amount;
        $costs->description = $request->description;
        if ($request->filled('created_at')) {
            $costs->created_at = \Carbon\Carbon::parse($request->created_at);
        }
        $costs->save();

        return new CostsWithUserResource($costs);
    }

    public function edit(Costs $costs, CostsRequest $request)
    {
        $costs->owner_id = $request->user()->id;
        $costs->amount = $request->amount;
        $costs->description = $request->description;
        if ($request->filled('created_at')) {
            $costs->created_at = \Carbon\Carbon::parse($request->created_at);
        }
        $costs->save();

        return new CostsWithUserResource($costs);
    }

    public function delete(Costs $costs)
    {
        $costs->delete();

        return success(message: 'Успішно видалено ');
    }

    public function list(Request $request)
    {
        $costs = Costs::query()->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return success(data: CostsWithUserResource::collection($costs));
    }
}
