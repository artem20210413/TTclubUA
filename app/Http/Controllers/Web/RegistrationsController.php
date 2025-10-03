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
use Illuminate\Http\Request;
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
    public function register()
    {
        return view('welcome.register');
    }

    public function apply(Request $request)
    {
        $request->validate([
            'full_name' => ['required','string','max:255'],
            'phone' => ['required','string','max:50'],
            'birthday' => ['required','date'],
            'city' => ['required','exists:cities,id'],
            'why_tt' => ['required','string','min:5'],
            'bio' => ['required','string','min:5'],
            'profile_photo' => ['required','image','max:4096'],

            'password' => ['required','confirmed','min:6'],
            // поле confirm должно называться password_confirmation — у тебя так и есть

            'car.model' => ['required','exists:car_models,id'],
            'car.gen' => ['nullable','exists:car_genes,id'],
            'car.plate' => ['required','string','max:20'],
            'car.vanity' => ['nullable','string','max:20'],
            'car.year' => ['nullable','integer','between:1998,'.now()->year],
            'car.color' => ['required','exists:colors,id'],
            'car.photo' => ['nullable','image','max:8192'],
        ]);

        // TODO: сохранить в БД, загрузить файлы, отправить уведомление

        return back()->with('status', 'Заявка відправлена! Ми зв’яжемося з вами.');
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
