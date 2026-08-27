<?php

namespace App\Http\Controllers;

use App\Enum\EnumTelegramEvents;
use App\Http\Requests\PurchaseRequestFormRequest;
use App\Models\Goods;
use App\Notifications\PurchaseRequestNotification;
use App\Notifications\Support\TelegramRecipients;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class PurchaseRequestController extends Controller
{
    public function send(PurchaseRequestFormRequest $request)
    {
        $goods = Goods::findOrFail($request->input('goods_id'));

        Notification::send(
            TelegramRecipients::routes(EnumTelegramEvents::PURCHASE_REQUEST->getIds()),
            new PurchaseRequestNotification(Auth::user(), $goods, $request->input('description'))
        );

        return response()->json(['message' => __('Заявку на покупку успішно відправлено! З вами зв\'яжеться менеджер.')]);
    }
}
