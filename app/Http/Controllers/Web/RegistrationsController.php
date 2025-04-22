<?php

namespace App\Http\Controllers\Web;

use App\Eloquent\CarEloquent;
use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Car\AddCollectionsCarRequest;
use App\Http\Requests\Car\CreateCarRequest;
use App\Http\Requests\Car\UpdateCarRequest;
use App\Http\Resources\Car\CarResource;
use App\Http\Resources\Car\CarWithUserResource;
use App\Http\Resources\Car\ColorResource;
use App\Http\Resources\Car\GenesResource;
use App\Http\Resources\Car\ModelResource;
use App\Models\Car;
use App\Models\CarGene;
use App\Models\CarModel;
use App\Models\Color;
use App\Models\User;
use App\Services\Image\ImageWebpService;
use Illuminate\Http\Request;


class RegistrationsController extends Controller
{
    public function index()
    {
        return view('welcome.welcomeForm');
    }
}
