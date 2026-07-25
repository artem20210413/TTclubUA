<?php

use App\Enum\EnumTypeMedia;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

function actingUser(): User
{
    $user = User::factory()->create();
    test()->actingAs($user);

    return $user;
}

// US1: aggregated city list for the map

it('excludes empty cities and returns correct user counts', function () {
    actingUser();

    $emptyCity = City::factory()->create();
    $singleUserCity = City::factory()->create();
    $singleUserCity->users()->attach(User::factory()->create());
    $multiUserCity = City::factory()->create();
    $multiUserCity->users()->attach(User::factory()->count(5)->create());

    $response = $this->getJson('/api/cities/map');

    $response->assertOk();
    $cities = collect($response->json('data.cities'));

    expect($cities->pluck('id'))->not->toContain($emptyCity->id);

    $single = $cities->firstWhere('id', $singleUserCity->id);
    expect($single['users_count'])->toBe(1);

    $multi = $cities->firstWhere('id', $multiUserCity->id);
    expect($multi['users_count'])->toBe(5);
});

it('returns avatar only for single-user cities', function () {
    actingUser();

    $singleUserCity = City::factory()->create();
    $singleUser = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');
    $singleUser->addMedia($file)->toMediaCollection(EnumTypeMedia::PROFILE_PICTURE->value);
    $singleUserCity->users()->attach($singleUser);

    $multiUserCity = City::factory()->create();
    $multiUserCity->users()->attach(User::factory()->count(2)->create());

    $response = $this->getJson('/api/cities/map');

    $cities = collect($response->json('data.cities'));

    $single = $cities->firstWhere('id', $singleUserCity->id);
    expect($single['avatar'])->not->toBeNull();
    expect($single['avatar'])->toBe($singleUser->getFirstMediaUrl(EnumTypeMedia::PROFILE_PICTURE->value));

    $multi = $cities->firstWhere('id', $multiUserCity->id);
    expect($multi['avatar'] ?? null)->toBeNull();
});

it('returns null avatar (not an empty string) for a single user with no uploaded avatar', function () {
    actingUser();

    $singleUserCity = City::factory()->create();
    $singleUserCity->users()->attach(User::factory()->create());

    $response = $this->getJson('/api/cities/map');

    $single = collect($response->json('data.cities'))->firstWhere('id', $singleUserCity->id);
    expect($single['avatar'])->toBeNull();
});

it('issues a constant number of queries regardless of city count', function () {
    actingUser();

    foreach (range(1, 20) as $i) {
        $city = City::factory()->create();
        $city->users()->attach(User::factory()->create());
    }

    $queryCount = 0;
    DB::listen(function () use (&$queryCount) {
        $queryCount++;
    });

    $this->getJson('/api/cities/map')->assertOk();

    expect($queryCount)->toBeLessThan(10);
});

// US2: paginated per-city user list

it('paginates the list of users in a city', function () {
    actingUser();

    $city = City::factory()->create();
    $city->users()->attach(User::factory()->count(25)->create());

    $page1 = $this->getJson('/api/cities/'.$city->id.'/users?page=1');
    $page1->assertOk();
    expect($page1->json('data'))->toHaveCount(15);
    expect($page1->json('meta.total'))->toBe(25);
    expect($page1->json('meta.last_page'))->toBe(2);

    $page2 = $this->getJson('/api/cities/'.$city->id.'/users?page=2');
    $page2->assertOk();
    expect($page2->json('data'))->toHaveCount(10);
});

it('returns an empty page for an unknown or empty city instead of an error', function () {
    actingUser();

    $emptyCity = City::factory()->create();

    $unknown = $this->getJson('/api/cities/999999/users');
    $unknown->assertOk();
    expect($unknown->json('data'))->toBe([]);
    expect($unknown->json('meta.total'))->toBe(0);

    $empty = $this->getJson('/api/cities/'.$emptyCity->id.'/users');
    $empty->assertOk();
    expect($empty->json('data'))->toBe([]);
    expect($empty->json('meta.total'))->toBe(0);
});
