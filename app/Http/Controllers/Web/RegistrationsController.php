<?php

namespace App\Http\Controllers\Web;

use App\Enum\EnumImageQuality;
use App\Enum\EnumTypeMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegiserFormRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Jobs\SandRegistrationToTg;
use App\Models\Registration;
use App\Services\Image\ImageWebpService;
use App\Services\Telegram\Sand\RegistrationSandToTo;
use Illuminate\Support\Facades\Redirect;


class RegistrationsController extends Controller
{
    public function indexOld()
    {
        return view('welcome.welcomeForm');
    }
    public function index()
    {
        return view('welcome.welcome');
    }
    public function indexForm()
    {
        return view('welcome.form');
    }

    public function registration(RegiserFormRequest $request)
    {
//        dd($request->all());

        $r = new Registration();
        $r->name = $request->get('name');
        $r->setPhone($request->get('phone'));
        $r->setPassword($request->get('password'));
        $r->generationJsom($request);
        $r->save();

        if ($image = $request->file('file')) {
            $imageWebp = new ImageWebpService($image);
            $imageWebp->convert(EnumImageQuality::LOW);
            $imageWebp->save($r, EnumTypeMedia::PROFILE_PICTURE);
        }

        $carFiles = $request->file('car_files');
        foreach ($request->cars as $key => $car) {
            $carFile = $carFiles[$key] ?? null;
            if (!isset($carFile)) continue;
            $imageWebp = new ImageWebpService($carFile);
            $imageWebp->convert(EnumImageQuality::LOW);
            $imageWebp->save($r, EnumTypeMedia::PHOTO_COLLECTION);
        }


        new RegistrationSandToTo($r);
//        SandRegistrationToTg::dispatch($r);
        return Redirect::route('web.home')->with(['message' => 'Заявка успішно створено, чекайте на підтвердження']);
    }
}
