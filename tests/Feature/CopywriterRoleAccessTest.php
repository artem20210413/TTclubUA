<?php

use App\Models\Draw;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Goods;
use App\Models\Partner;
use App\Models\Prize;
use App\Models\User;
use Spatie\Permission\Models\Role;

function userWithRole(string $role): User
{
    Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

function createEventType(): EventType
{
    $eventType = new EventType();
    $eventType->forceFill(['name' => 'Test Type', 'alias' => 'test-type-'.uniqid(), 'sort' => 0]);
    $eventType->save();

    return $eventType;
}

function createGoods(): Goods
{
    $goods = new Goods();
    $goods->title = 'Test Goods';
    $goods->description = 'Description';
    $goods->priority = 0;
    $goods->save();

    return $goods;
}

// US1: copywriter — create/update дозволено, delete/roll/reset заборонено (403)

it('allows copywriter to create and update a partner but not delete its photo', function () {
    userWithRole('copywriter');

    $this->postJson('/api/partners', ['title' => 'Test Partner'])->assertStatus(201);

    $partner = Partner::first();
    $this->postJson("/api/partners/{$partner->id}", ['title' => 'Updated Partner'])->assertStatus(200);

    $this->deleteJson("/api/partners/{$partner->id}/photos/999")->assertStatus(403);
});

it('allows copywriter to create and update an event but not delete its image', function () {
    userWithRole('copywriter');

    $eventType = createEventType();

    $this->postJson('/api/event', [
        'title' => 'Test Event',
        'description' => 'Description',
        'place' => 'Test Place',
        'event_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        'event_type_id' => $eventType->id,
    ])->assertStatus(200);

    $event = Event::first();
    $this->putJson("/api/event/{$event->id}", [
        'title' => 'Updated Event',
        'description' => 'Description',
        'place' => 'Test Place',
        'event_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        'event_type_id' => $eventType->id,
    ])->assertStatus(200);

    $this->deleteJson("/api/event/{$event->id}/image")->assertStatus(403);
});

it('allows copywriter to create and update goods but not delete its images', function () {
    userWithRole('copywriter');

    $this->postJson('/api/goods', [
        'title' => 'Test Goods Created',
        'description' => 'Description',
        'priority' => 0,
    ])->assertStatus(200);

    $goods = Goods::first();
    $this->putJson("/api/goods/{$goods->id}", [
        'title' => 'Updated Goods',
        'description' => 'Description',
        'priority' => 0,
        'active' => 1,
    ])->assertStatus(200);

    $this->deleteJson("/api/goods/{$goods->id}/images")->assertStatus(403);
});

it('allows copywriter to create and update a draw and prize but not delete or roll/reset', function () {
    userWithRole('copywriter');

    $this->postJson('/api/draws', ['title' => 'Test Draw'])->assertStatus(200);

    $draw = Draw::first();
    $this->putJson("/api/draws/{$draw->id}", ['title' => 'Updated Draw'])->assertStatus(200);
    $this->deleteJson("/api/draws/{$draw->id}")->assertStatus(403);

    $this->postJson("/api/draws/{$draw->id}/prizes", ['title' => 'Test Prize'])->assertStatus(201);

    $prize = Prize::first();
    $this->putJson("/api/draws/{$draw->id}/prizes/{$prize->id}", ['title' => 'Updated Prize'])->assertStatus(200);
    $this->deleteJson("/api/draws/{$draw->id}/prizes/{$prize->id}")->assertStatus(403);

    $this->postJson("/api/draws/{$draw->id}/roll/{$prize->id}")->assertStatus(403);
    $this->postJson("/api/draws/{$draw->id}/reset/{$prize->id}")->assertStatus(403);
});

it('forbids copywriter from accessing routes outside the four allowed sections', function () {
    userWithRole('copywriter');

    $this->postJson('/api/import')->assertStatus(403);
});

// US2: head-copywriter — усе, включно з видаленням і керуванням розіграшем

it('allows head-copywriter to reach the delete-partner-photo action (authorized, media not found)', function () {
    userWithRole('head-copywriter');

    $partner = Partner::create(['title' => 'Test Partner']);

    expect($this->deleteJson("/api/partners/{$partner->id}/photos/999")->status())->not->toBe(403);
});

it('allows head-copywriter to delete an event image', function () {
    userWithRole('head-copywriter');

    $eventType = createEventType();
    $event = new Event();
    $event->title = 'Test Event';
    $event->description = 'Description';
    $event->place = 'Test Place';
    $event->event_date = now()->addDays(3);
    $event->event_type_id = $eventType->id;
    $event->active = 1;
    $event->save();

    $this->deleteJson("/api/event/{$event->id}/image")->assertStatus(200);
});

it('allows head-copywriter to delete goods images', function () {
    userWithRole('head-copywriter');

    $goods = createGoods();

    $this->deleteJson("/api/goods/{$goods->id}/images")->assertStatus(200);
});

it('allows head-copywriter to delete a draw, roll and reset a prize', function () {
    userWithRole('head-copywriter');

    $draw = Draw::create(['title' => 'Test Draw']);
    $prize = Prize::create(['draw_id' => $draw->id, 'title' => 'Test Prize']);

    expect($this->postJson("/api/draws/{$draw->id}/roll/{$prize->id}")->status())->not->toBe(403);
    expect($this->postJson("/api/draws/{$draw->id}/reset/{$prize->id}")->status())->not->toBe(403);

    expect($this->deleteJson("/api/draws/{$draw->id}/prizes/{$prize->id}")->status())->not->toBe(403);
    expect($this->deleteJson("/api/draws/{$draw->id}")->status())->not->toBe(403);
});

it('forbids head-copywriter from accessing routes outside the four allowed sections', function () {
    userWithRole('head-copywriter');

    $this->postJson('/api/import')->assertStatus(403);
});
