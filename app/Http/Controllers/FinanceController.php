<?php

namespace App\Http\Controllers;

use App\Eloquent\FinanceEloquent;
use App\Enum\EnumMonoAccount;
use App\Enum\EnumMonoStatus;
use App\Http\Controllers\Api\ApiException;
use App\Http\Requests\FinanceRequest;
use App\Http\Resources\FinanceWithUserResource;
use App\Models\Finance;
use App\Models\MonoTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinanceController extends Controller
{

    public function set(User $user, FinanceRequest $request)
    {
        $finance = new Finance();
        $finance->user_id = $user->id;
        $finance->amount = $request->amount;
        $finance->description = $request->description;
        $finance->save();

        return new FinanceWithUserResource($finance);
    }

    public function delete(Finance $finance)
    {
        $finance->delete();

        return success(message: 'Успішно видалено ');
    }

    public function list(User $user, Request $request)
    {
        $f = Finance::query()->where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(15);
        return success(data: FinanceWithUserResource::collection($f));
    }

    public function statistics(User $user, Request $request)
    {
        return success(data: [
            'all_sum' => Finance::query()->where('user_id', $user->id)->sum('amount'),
//            'last_year' => Finance::query()->where('user_id', $user->id)->where('created_at', '>=', now()->startOfYear())->sum('amount'),
            'last_year' => Finance::query()->where('user_id', $user->id)->where('created_at', '>=', now()->subDays(365))->sum('amount'),
//            'last_month' => Finance::query()->where('user_id', $user->id)->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'last_month' => Finance::query()->where('user_id', $user->id)->where('created_at', '>=', now()->subDays(30))->sum('amount'),
            'total_payments_count' => Finance::where('user_id', $user->id)->count(), // сколько всего платежей сделал пользователь
            'average_payment' => number_format(
                Finance::where('user_id', $user->id)->avg('amount') ?? 0,
                2,
                '.',
                ''
            ),
            'largest_payment' => Finance::where('user_id', $user->id)->max('amount'), // наибольший платёж
            'smallest_payment' => Finance::where('user_id', $user->id)->min('amount'), // наименьший платёж
            'last_payment_date' => Finance::where('user_id', $user->id)->latest()->value('created_at'), // дата последнего платежа
            'first_payment_date' => Finance::where('user_id', $user->id)->oldest()->value('created_at'),// когда был сделан первый платёж

        ]);
    }


    public function webhookMonobank(Request $request)
    {
        $monoAccount = EnumMonoAccount::TEST;

        Log::info('webhookMonobank', ['body' => $request->all(), 'headers' => $request->header(), 'ip' => $request->ip(), 'host' => $request->host()]);
        $statementItem = $request->data['statementItem'] ?? null;

        if ($request->all() === [] || ($request->data['account'] ?? null) !== $monoAccount->getID() || !$statementItem)
            return success();

        $description = $statementItem['comment'] ?? '';
        if (preg_match('/pay:([a-zA-Z0-9]+)/', $description, $matches)) {
            $hash = $matches[1];
            /** @var MonoTransaction $payment */
            $payment = MonoTransaction::where('hash', $hash)->first();
            if ($payment) {
                $payment->status = EnumMonoStatus::CONFIRMED->getAlias();
                $payment->currency_code = $statementItem['currency_code'] ?? null;
                $payment->amount = $statementItem['amount'] ?? null;
                $payment->description = $statementItem['description'] ?? null;
                $payment->comment = $statementItem['comment'] ?? null;
                $payment->save();

                FinanceEloquent::createByMono($payment);
                return success();
            }
        }

        $payment = new MonoTransaction();
        $payment->jar_id = $monoAccount->getID();
        $payment->status = EnumMonoStatus::CONFIRMED->getAlias();
        $payment->currency_code = $statementItem['currency_code'] ?? null;
        $payment->amount = $statementItem['amount'] ?? null;
        $payment->comment = $statementItem['comment'] ?? null;
        $payment->description = $statementItem['description'] ?? null;
        $payment->save();

        return success();
    }

    public function redirectJarMonobank(Request $request)
    {
        $user = User::find($request->userId);
        $monoAccount = EnumMonoAccount::TEST;
        $baseUrl = "https://send.monobank.ua/jar/{$monoAccount->getSendId()}";

        $isApi = $request->is('api/*');
        if (!$user) {
            return $isApi
                ? error(new ApiException('User not found'))
                : redirect($baseUrl);
        }

        $transaction = new MonoTransaction();
        $transaction->createHash($user, $monoAccount->getID());
        $transaction->jar_id = $monoAccount->getID();
        $transaction->user_id = $user->id;
        $transaction->save();
        $url = $baseUrl . '?' . http_build_query([
                't' => 'pay:' . $transaction->hash,
            ]);

        return $isApi
            ? success(data: ['url' => $url])
            : redirect($url);
    }


}
