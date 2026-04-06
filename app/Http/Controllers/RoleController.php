<?php

namespace App\Http\Controllers;

use App\Enum\EnumUserRoles;

class RoleController extends Controller
{
    public function index()
    {
        return success(data: EnumUserRoles::apiList());
    }
}
