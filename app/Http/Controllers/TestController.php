<?php

namespace App\Http\Controllers;

use App\Console\Commands\Tg\SendingStatisticsMention;
use App\Eloquent\MentionEloquent;
use App\Enum\EnumTelegramEvents;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\User\ChangePasswordByUserRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Jobs\Autoria\SyncCarDetailJob;
use App\Models\Mention;
use App\Models\User;
use App\Services\Fcm\Fcm;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function test(Request $request)
    {
//        Fcm::sendPush('fuYEPdSPSUqY7oFiA8tCZF:APA91bESTWAT4ArtQKEsijsWzt6SZn6jLvOecj6MjopXOiuC0I36NeYhQo9oDjyNehlUPEDwM3TvwxNcprSl0L0ZxW15w9MH3YxRZlbY65RtC2WwLf2fNGI2');
//        $h = new SendingStatisticsMention();
//        $h->handle();
//        dd(33);

    }

}
