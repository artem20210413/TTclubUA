<?php

use App\Models\Goods;
use App\Models\TelegramMessage;
use App\Models\User;
use Telegram\Bot\Api;

function actingPurchaseUser(): User
{
    $user = User::factory()->create();
    test()->actingAs($user);

    return $user;
}

function makeGoods(string $title, float $price, bool $active): Goods
{
    $goods = new Goods;
    $goods->title = $title;
    $goods->description = '';
    $goods->price = $price;
    $goods->active = $active;
    $goods->save();

    return $goods;
}

function mockTelegramApi(): Api
{
    $api = Mockery::mock(Api::class);
    app()->instance(Api::class, $api);

    return $api;
}

it('accepts a purchase request without a description and notifies telegram', function () {
    actingPurchaseUser();
    config(['telegram.chats.purchase_requests' => 'test-chat']);

    $goods = makeGoods('Диски R18', 1500, true);

    $api = mockTelegramApi();
    $api->shouldReceive('sendMessage')
        ->once()
        ->withArgs(fn (array $params) => $params['chat_id'] === 'test-chat'
            && str_contains($params['text'], 'Диски R18')
            && str_contains($params['text'], '1500'))
        ->andReturn([]);

    $response = $this->postJson('/api/goods/purchase-request', ['goods_id' => $goods->id]);

    $response->assertOk();
    expect(TelegramMessage::where('chat_id', 'test-chat')->exists())->toBeTrue();
});

it('includes the description in the notification when provided', function () {
    actingPurchaseUser();
    config(['telegram.chats.purchase_requests' => 'test-chat']);

    $goods = makeGoods('Спойлер', 3000, true);

    $api = mockTelegramApi();
    $api->shouldReceive('sendMessage')
        ->once()
        ->withArgs(fn (array $params) => str_contains($params['text'], 'Хочу забрати в п\'ятницю'))
        ->andReturn([]);

    $response = $this->postJson('/api/goods/purchase-request', [
        'goods_id' => $goods->id,
        'description' => "Хочу забрати в п'ятницю",
    ]);

    $response->assertOk();
});

it('rejects a purchase request for a non-existent product', function () {
    actingPurchaseUser();

    $api = mockTelegramApi();
    $api->shouldNotReceive('sendMessage');

    $response = $this->postJson('/api/goods/purchase-request', ['goods_id' => 999999]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('goods_id');
});

it('rejects an unauthenticated purchase request', function () {
    $goods = makeGoods('Капот', 5000, true);

    $api = mockTelegramApi();
    $api->shouldNotReceive('sendMessage');

    $response = $this->postJson('/api/goods/purchase-request', ['goods_id' => $goods->id]);

    $response->assertStatus(401);
});

it('rejects a description longer than 500 characters', function () {
    actingPurchaseUser();

    $goods = makeGoods('Дзеркало', 800, true);

    $api = mockTelegramApi();
    $api->shouldNotReceive('sendMessage');

    $response = $this->postJson('/api/goods/purchase-request', [
        'goods_id' => $goods->id,
        'description' => str_repeat('a', 501),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('description');
});

it('flags an inactive product in the notification but still accepts the request', function () {
    actingPurchaseUser();
    config(['telegram.chats.purchase_requests' => 'test-chat']);

    $goods = makeGoods('Стара деталь', 100, false);

    $api = mockTelegramApi();
    $api->shouldReceive('sendMessage')
        ->once()
        ->withArgs(fn (array $params) => str_contains($params['text'], 'неактивний'))
        ->andReturn([]);

    $response = $this->postJson('/api/goods/purchase-request', ['goods_id' => $goods->id]);

    $response->assertOk();
});

it('still returns success and logs the failure when telegram delivery fails', function () {
    actingPurchaseUser();
    config(['telegram.chats.purchase_requests' => 'test-chat']);

    $goods = makeGoods('Фара', 2200, true);

    $api = mockTelegramApi();
    $api->shouldReceive('sendMessage')
        ->once()
        ->andThrow(new \RuntimeException('Telegram is down'));

    $response = $this->postJson('/api/goods/purchase-request', ['goods_id' => $goods->id]);

    $response->assertOk();
    expect(TelegramMessage::where('chat_id', 'test-chat')->exists())->toBeTrue();
});
