<?php

use App\Models\Costs;
use App\Models\Finance;
use App\Models\User;
use Illuminate\Support\Carbon;

function actingUserForFinanceStats(): User
{
    $user = User::factory()->create();
    test()->actingAs($user);

    return $user;
}

function currentSeasonStart(): Carbon
{
    $startMonth = config('club.season_start_month', 9);
    $now = now();

    return Carbon::create($now->month < $startMonth ? $now->year - 1 : $now->year, $startMonth, 1)->startOfDay();
}

it('rejects unauthenticated requests with 401', function () {
    $this->getJson('/api/finance/statistics/seasons')->assertUnauthorized();
});

it('returns the current season by default with correct totals', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    Finance::factory()->create(['created_at' => $currentStart->copy()->addDays(5), 'amount' => 900]);
    Costs::factory()->create(['created_at' => $currentStart->copy()->addDays(15), 'amount' => 1200]);

    $response = $this->getJson('/api/finance/statistics/seasons');
    $response->assertOk();
    $season = $response->json('data');

    expect($season['season_start'])->toBe($currentStart->format('Y-m-d'));
    expect($season['season_end'])->toBeNull();
    expect((float) $season['spent_total'])->toBe(1200.0);
    expect((float) $season['collected_total'])->toBe(900.0);
});

it('carries a shortfall balance forward from a prior season', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    $previousStart = $currentStart->copy()->subYear();

    Finance::factory()->create(['created_at' => $previousStart->copy()->addDays(10), 'amount' => 1850]);
    Costs::factory()->create(['created_at' => $previousStart->copy()->addDays(20), 'amount' => 2000]);

    $response = $this->getJson('/api/finance/statistics/seasons');
    $season = $response->json('data');

    expect((float) $season['opening_balance'])->toBe(-150.0);
});

it('paginates the itemized cost list for the season', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    Costs::factory()->count(25)->create(['created_at' => $currentStart->copy()->addDays(1)]);

    $response = $this->getJson('/api/finance/statistics/seasons');
    $response->assertOk();
    $season = $response->json('data');

    expect($season['costs']['per_page'])->toBe(20);
    expect($season['costs']['total'])->toBe(25);
    expect($season['costs']['last_page'])->toBe(2);
    expect($season['costs']['data'])->toHaveCount(20);

    $responsePerPage1 = $this->getJson('/api/finance/statistics/seasons?per_page=1');
    $seasonPerPage1 = $responsePerPage1->json('data');
    expect($seasonPerPage1['costs']['last_page'])->toBe(25);
});

it('reports zero totals when the season has records on only one side', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    Costs::factory()->create(['created_at' => $currentStart->copy()->addDays(1), 'amount' => 500]);

    $response = $this->getJson('/api/finance/statistics/seasons');
    $season = $response->json('data');

    expect((float) $season['spent_total'])->toBe(500.0);
    expect((float) $season['collected_total'])->toBe(0.0);
});

it('rejects an invalid year parameter', function () {
    actingUserForFinanceStats();

    $this->getJson('/api/finance/statistics/seasons?year=abc')->assertStatus(422);
    $this->getJson('/api/finance/statistics/seasons?year=1999')->assertStatus(422);
});

it('returns exactly the requested season when year is given', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    $requestedYear = $currentStart->year - 1;
    $requestedSeasonStart = $currentStart->copy()->subYear();

    Finance::factory()->create(['created_at' => $requestedSeasonStart->copy()->addDays(10), 'amount' => 700]);
    Costs::factory()->create(['created_at' => $requestedSeasonStart->copy()->addDays(20), 'amount' => 400]);
    // noise in the current season that must NOT be included
    Finance::factory()->create(['created_at' => $currentStart->copy()->addDays(5), 'amount' => 900]);

    $response = $this->getJson("/api/finance/statistics/seasons?year={$requestedYear}");
    $response->assertOk();
    $season = $response->json('data');

    expect($season['season_start'])->toBe($requestedSeasonStart->format('Y-m-d'));
    expect($season['season_end'])->toBe($requestedSeasonStart->copy()->addYear()->format('Y-m-d'));
    expect((float) $season['collected_total'])->toBe(700.0);
    expect((float) $season['spent_total'])->toBe(400.0);
});

it('returns a null season_end when year is the current season year', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();

    $response = $this->getJson("/api/finance/statistics/seasons?year={$currentStart->year}");
    $response->assertOk();

    expect($response->json('data.season_end'))->toBeNull();
});
