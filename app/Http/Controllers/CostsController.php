<?php

namespace App\Http\Controllers;

use App\Eloquent\FinanceEloquent;
use App\Enum\EnumMonoAccount;
use App\Enum\EnumMonoStatus;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\CostsRequest;
use App\Http\Requests\FinanceRequest;
use App\Http\Resources\CostsWithUserResource;
use App\Http\Resources\FinanceWithUserResource;
use App\Models\Costs;
use App\Models\Finance;
use App\Models\MonoTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CostsController extends Controller
{

    public function set(CostsRequest $request)
    {

        $costs = new Costs();
        $costs->owner_id = $request->user()->id;
        $costs->amount = $request->amount;
        $costs->description = $request->description;
        $costs->save();

        return new CostsWithUserResource($costs);
    }

    public function edit(Costs $costs, CostsRequest $request)
    {
        $costs->owner_id = $request->user()->id;
        $costs->amount = $request->amount;
        $costs->description = $request->description;
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
