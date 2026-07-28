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

// US1: view spending vs. contributions by season

it('rejects unauthenticated requests with 401', function () {
    $this->getJson('/api/finance/statistics/seasons')->assertUnauthorized();
});

it('returns default 2 seasons with correct totals per season', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    $previousStart = $currentStart->copy()->subYear();

    // previous season: shortfall (spent > collected)
    Finance::factory()->create(['created_at' => $previousStart->copy()->addDays(10), 'amount' => 1850]);
    Costs::factory()->create(['created_at' => $previousStart->copy()->addDays(20), 'amount' => 2000]);

    // current season
    Finance::factory()->create(['created_at' => $currentStart->copy()->addDays(5), 'amount' => 900]);
    Costs::factory()->create(['created_at' => $currentStart->copy()->addDays(15), 'amount' => 1200]);

    $response = $this->getJson('/api/finance/statistics/seasons');

    $response->assertOk();
    $seasons = $response->json('data.seasons');

    expect($seasons)->toHaveCount(2);
    expect($seasons[0]['season_start'])->toBe($currentStart->format('Y-m-d'));
    expect($seasons[1]['season_start'])->toBe($previousStart->format('Y-m-d'));

    expect((float) $seasons[1]['spent_total'])->toBe(2000.0);
    expect((float) $seasons[1]['collected_total'])->toBe(1850.0);
    expect((float) $seasons[0]['spent_total'])->toBe(1200.0);
    expect((float) $seasons[0]['collected_total'])->toBe(900.0);
});

it('carries a shortfall balance forward into the next season', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    $previousStart = $currentStart->copy()->subYear();

    Finance::factory()->create(['created_at' => $previousStart->copy()->addDays(10), 'amount' => 1850]);
    Costs::factory()->create(['created_at' => $previousStart->copy()->addDays(20), 'amount' => 2000]);

    $response = $this->getJson('/api/finance/statistics/seasons');
    $seasons = $response->json('data.seasons');

    $previousSeason = collect($seasons)->firstWhere('season_start', $previousStart->format('Y-m-d'));
    $currentSeason = collect($seasons)->firstWhere('season_start', $currentStart->format('Y-m-d'));

    expect((float) $previousSeason['closing_balance'])->toBe(-150.0);
    expect((float) $currentSeason['opening_balance'])->toBe(-150.0);
});

it('paginates the itemized cost list per season', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    Costs::factory()->count(25)->create(['created_at' => $currentStart->copy()->addDays(1)]);

    $response = $this->getJson('/api/finance/statistics/seasons?seasons=1');
    $response->assertOk();
    $season = $response->json('data.seasons.0');

    expect($season['costs']['per_page'])->toBe(20);
    expect($season['costs']['total'])->toBe(25);
    expect($season['costs']['last_page'])->toBe(2);
    expect($season['costs']['data'])->toHaveCount(20);

    $responsePerPage1 = $this->getJson('/api/finance/statistics/seasons?seasons=1&per_page=1');
    $seasonPerPage1 = $responsePerPage1->json('data.seasons.0');
    expect($seasonPerPage1['costs']['last_page'])->toBe(25);
});

it('reports zero totals when a season has records on only one side', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    Costs::factory()->create(['created_at' => $currentStart->copy()->addDays(1), 'amount' => 500]);

    $response = $this->getJson('/api/finance/statistics/seasons?seasons=1');
    $season = $response->json('data.seasons.0');

    expect((float) $season['spent_total'])->toBe(500.0);
    expect((float) $season['collected_total'])->toBe(0.0);
});

// US2: adjust the lookback period

it('returns exactly 1 season when seasons=1', function () {
    actingUserForFinanceStats();

    $response = $this->getJson('/api/finance/statistics/seasons?seasons=1');
    $response->assertOk();
    expect($response->json('data.seasons'))->toHaveCount(1);
});

it('returns up to 5 seasons when seasons=5', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    for ($i = 0; $i < 5; $i++) {
        Finance::factory()->create(['created_at' => $currentStart->copy()->subYears($i)->addDays(5), 'amount' => 100]);
    }

    $response = $this->getJson('/api/finance/statistics/seasons?seasons=5');
    $response->assertOk();
    expect($response->json('data.seasons'))->toHaveCount(5);
});

it('rejects invalid seasons parameter values', function () {
    actingUserForFinanceStats();

    $this->getJson('/api/finance/statistics/seasons?seasons=0')->assertStatus(422);
    $this->getJson('/api/finance/statistics/seasons?seasons=-1')->assertStatus(422);
    $this->getJson('/api/finance/statistics/seasons?seasons=abc')->assertStatus(422);
});

it('returns fewer seasons than requested when history is shorter', function () {
    actingUserForFinanceStats();

    $response = $this->getJson('/api/finance/statistics/seasons?seasons=10');
    $response->assertOk();
    expect(count($response->json('data.seasons')))->toBeLessThan(10);
});

// US3: overall coverage across the full period

it('returns a grand total matching the sum of included seasons', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    $previousStart = $currentStart->copy()->subYear();

    Finance::factory()->create(['created_at' => $previousStart->copy()->addDays(10), 'amount' => 1850]);
    Costs::factory()->create(['created_at' => $previousStart->copy()->addDays(20), 'amount' => 2000]);
    Finance::factory()->create(['created_at' => $currentStart->copy()->addDays(5), 'amount' => 900]);
    Costs::factory()->create(['created_at' => $currentStart->copy()->addDays(15), 'amount' => 1200]);

    $response = $this->getJson('/api/finance/statistics/seasons');
    $data = $response->json('data');

    expect((float) $data['grand_total']['spent_total'])->toBe(3200.0);
    expect((float) $data['grand_total']['collected_total'])->toBe(2750.0);
    expect((float) $data['grand_total']['closing_balance'])->toBe((float) $data['seasons'][0]['closing_balance']);
});

it('grand total closing balance reflects history before the requested window', function () {
    actingUserForFinanceStats();

    $currentStart = currentSeasonStart();
    $twoSeasonsAgoStart = $currentStart->copy()->subYears(2);

    // shortfall in a season outside the requested window (seasons=1 only shows current)
    Finance::factory()->create(['created_at' => $twoSeasonsAgoStart->copy()->addDays(10), 'amount' => 100]);
    Costs::factory()->create(['created_at' => $twoSeasonsAgoStart->copy()->addDays(20), 'amount' => 300]);

    $response = $this->getJson('/api/finance/statistics/seasons?seasons=1');
    $data = $response->json('data');

    expect((float) $data['grand_total']['closing_balance'])->toBe(-200.0);
    expect((float) $data['seasons'][0]['opening_balance'])->toBe(-200.0);
});
