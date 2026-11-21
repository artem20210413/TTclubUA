<?php

use App\Enum\EnumMonoAccount;
use App\Models\Finance;
use App\Models\MonoTransaction;
use App\Models\User;
use Telegram\Bot\Api;

beforeEach(function () {
    config(['telegram.chats.welcome' => 'admin-chat']);

    $this->telegram = Mockery::mock(Api::class);
    app()->instance(Api::class, $this->telegram);
});

function pendingMonoTransaction(User $user): MonoTransaction
{
    $transaction = new MonoTransaction;
    $transaction->createHash($user, EnumMonoAccount::LILIIA->getID());
    $transaction->jar_id = EnumMonoAccount::LILIIA->getID();
    $transaction->user_id = $user->id;
    $transaction->save();

    return $transaction;
}

function monobankWebhookPayload(string $statementId, ?string $comment, int $amount = 50000, string $description = 'Від: 🐈'): array
{
    return [
        'type' => 'StatementItem',
        'data' => [
            'account' => EnumMonoAccount::LILIIA->getID(),
            'statementItem' => array_filter([
                'id' => $statementId,
                'time' => 1763581548,
                'description' => $description,
                'amount' => $amount,
                'currencyCode' => 980,
                'comment' => $comment,
            ], fn ($value) => $value !== null),
        ],
    ];
}

function expectUnmatchedPaymentAlert(Mockery\MockInterface $telegram, string ...$fragments): void
{
    $telegram->shouldReceive('sendMessage')
        ->once()
        ->withArgs(function (array $params) use ($fragments) {
            if ($params['chat_id'] !== 'admin-chat' || ! str_contains($params['text'], 'Платіж у банку без власника')) {
                return false;
            }

            foreach ($fragments as $fragment) {
                if (! str_contains($params['text'], $fragment)) {
                    return false;
                }
            }

            return true;
        })
        ->andReturn([]);
}

it('confirms a pending transaction and credits the payment to its user', function () {
    $this->telegram->shouldNotReceive('sendMessage');
    $user = User::factory()->create();
    $transaction = pendingMonoTransaction($user);

    $this->postJson('/api/webhook/monobank', monobankWebhookPayload('stmt-1', 'pay:'.$transaction->hash))
        ->assertOk();

    $transaction->refresh();
    expect($transaction->status)->toBe('confirmed')
        ->and($transaction->statement_id)->toBe('stmt-1')
        ->and($transaction->amount)->toBe(50000)
        ->and($transaction->currency_code)->toBe(980);

    $finances = Finance::where('user_id', $user->id)->get();
    expect($finances)->toHaveCount(1)
        ->and((float) $finances->first()->amount)->toBe(500.0);
});

it('processes a redelivered webhook only once', function () {
    $this->telegram->shouldNotReceive('sendMessage');
    $user = User::factory()->create();
    $transaction = pendingMonoTransaction($user);
    $payload = monobankWebhookPayload('stmt-1', 'pay:'.$transaction->hash);

    $this->postJson('/api/webhook/monobank', $payload)->assertOk();
    $this->postJson('/api/webhook/monobank', $payload)->assertOk();

    expect(Finance::where('user_id', $user->id)->count())->toBe(1)
        ->and(MonoTransaction::count())->toBe(1);
});

it('does not credit the link owner when an already used link is paid again and alerts the admins', function () {
    $owner = User::factory()->create();
    $transaction = pendingMonoTransaction($owner);
    expectUnmatchedPaymentAlert($this->telegram, '1 000.00 грн', 'лінк уже оплачено раніше', "(#{$owner->id})");

    $this->postJson('/api/webhook/monobank', monobankWebhookPayload('stmt-1', 'pay:'.$transaction->hash))->assertOk();
    $this->postJson('/api/webhook/monobank', monobankWebhookPayload('stmt-2', 'pay:'.$transaction->hash, 100000))->assertOk();

    expect(Finance::where('user_id', $owner->id)->count())->toBe(1);

    $transaction->refresh();
    expect($transaction->statement_id)->toBe('stmt-1')
        ->and($transaction->amount)->toBe(50000);

    $orphan = MonoTransaction::where('statement_id', 'stmt-2')->first();
    expect($orphan->user_id)->toBeNull()
        ->and($orphan->status)->toBe('confirmed')
        ->and($orphan->amount)->toBe(100000)
        ->and($orphan->comment)->toBe('pay:'.$transaction->hash);
});

it('stores a payment with an unknown code without crediting anyone and alerts the admins', function () {
    expectUnmatchedPaymentAlert($this->telegram, '500.00 грн', 'код оплати не знайдено', 'pay:unknownhash');

    $this->postJson('/api/webhook/monobank', monobankWebhookPayload('stmt-1', 'pay:unknownhash'))->assertOk();

    $orphan = MonoTransaction::where('statement_id', 'stmt-1')->first();
    expect($orphan->user_id)->toBeNull()
        ->and($orphan->currency_code)->toBe(980)
        ->and(Finance::count())->toBe(0);
});

it('stores and reports a payment without a payment code, escaping the payer text', function () {
    expectUnmatchedPaymentAlert($this->telegram, 'без коду із застосунку', '<b>Відправник:</b> &lt;Tom &amp; Jerry&gt;');

    $this->postJson('/api/webhook/monobank', monobankWebhookPayload('stmt-1', null, description: 'Від: <Tom & Jerry>'))
        ->assertOk();

    $orphan = MonoTransaction::where('statement_id', 'stmt-1')->first();
    expect($orphan->user_id)->toBeNull()
        ->and($orphan->comment)->toBeNull()
        ->and(Finance::count())->toBe(0);
});

it('reports a redelivered unmatched payment only once', function () {
    expectUnmatchedPaymentAlert($this->telegram);
    $payload = monobankWebhookPayload('stmt-1', 'pay:unknownhash');

    $this->postJson('/api/webhook/monobank', $payload)->assertOk();
    $this->postJson('/api/webhook/monobank', $payload)->assertOk();

    expect(MonoTransaction::count())->toBe(1);
});

it('ignores outgoing jar operations', function () {
    $this->telegram->shouldNotReceive('sendMessage');

    $this->postJson('/api/webhook/monobank', monobankWebhookPayload('stmt-1', null, -100000, 'Виведення коштів'))
        ->assertOk();

    expect(MonoTransaction::count())->toBe(0);
});
