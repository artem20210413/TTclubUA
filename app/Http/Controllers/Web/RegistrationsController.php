<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegiserFormRequest;
use App\Http\Requests\User\RegisterRequest;


class RegistrationsController extends Controller
{
    public function index()
    {
        return view('welcome.welcomeForm');
    }
    public function registration(RegiserFormRequest $request)
    {
dd($request->all());

    }
}
