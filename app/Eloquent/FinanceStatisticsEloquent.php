<?php

namespace App\Eloquent;

use App\Models\Costs;
use App\Models\Finance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class FinanceStatisticsEloquent
{
    /**
     * Ordered list (most recent first) of season boundaries.
     * The most recent season has `end` = null (current, in-progress season).
     *
     * @return array<int, array{start: Carbon, end: ?Carbon}>
     */
    public static function seasonBoundaries(int $seasonsBack): array
    {
        $startMonth = config('club.season_start_month', 9);
        $now = now();
        $currentSeasonStart = Carbon::create(
            $now->month < $startMonth ? $now->year - 1 : $now->year,
            $startMonth,
            1
        )->startOfDay();

        $earliestRecordAt = self::earliestRecordDate();
        $earliestSeasonStart = $earliestRecordAt === null ? null : Carbon::create(
            $earliestRecordAt->month < $startMonth ? $earliestRecordAt->year - 1 : $earliestRecordAt->year,
            $startMonth,
            1
        )->startOfDay();

        $seasons = [];
        for ($i = 0; $i < $seasonsBack; $i++) {
            $start = $currentSeasonStart->copy()->subYears($i);

            // Always include the current season (i === 0); stop earlier once we're
            // past the club's earliest recorded history (per US2 acceptance scenario 2).
            // With no history at all, only the current season is shown.
            if ($i > 0 && ($earliestSeasonStart === null || $start->lt($earliestSeasonStart))) {
                break;
            }

            $end = $i === 0 ? null : $currentSeasonStart->copy()->subYears($i - 1);
            $seasons[] = ['start' => $start, 'end' => $end];
        }

        return $seasons;
    }

    private static function earliestRecordDate(): ?Carbon
    {
        $earliestFinance = Finance::query()->oldest('created_at')->value('created_at');
        $earliestCosts = Costs::query()->oldest('created_at')->value('created_at');

        $dates = array_filter([$earliestFinance, $earliestCosts]);
        if (empty($dates)) {
            return null;
        }

        return Carbon::parse(min($dates));
    }

    /**
     * Running balance (collected - spent) accumulated from all history strictly before $seasonStart.
     */
    public static function openingBalance(Carbon $seasonStart): float
    {
        $collected = (float) Finance::query()->where('created_at', '<', $seasonStart)->sum('amount');
        $spent = (float) Costs::query()->where('created_at', '<', $seasonStart)->sum('amount');

        return $collected - $spent;
    }

    /**
     * @return array{spent_total: float, collected_total: float}
     */
    public static function seasonTotals(Carbon $start, ?Carbon $end): array
    {
        $costsQuery = Costs::query()->where('created_at', '>=', $start);
        $financeQuery = Finance::query()->where('created_at', '>=', $start);

        if ($end !== null) {
            $costsQuery->where('created_at', '<', $end);
            $financeQuery->where('created_at', '<', $end);
        }

        return [
            'spent_total' => (float) $costsQuery->sum('amount'),
            'collected_total' => (float) $financeQuery->sum('amount'),
        ];
    }

    public static function paginatedCostsForSeason(Carbon $start, ?Carbon $end, int $perPage, int $page): LengthAwarePaginator
    {
        $query = Costs::query()->where('created_at', '>=', $start);

        if ($end !== null) {
            $query->where('created_at', '<', $end);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @return array{seasons: array<int, array>, grand_total: array{spent_total: float, collected_total: float, closing_balance: float}}
     */
    public static function buildSeasonStatistics(int $seasonsBack, int $perPage, int $page): array
    {
        $boundaries = self::seasonBoundaries($seasonsBack);

        $seasons = [];
        $grandSpent = 0.0;
        $grandCollected = 0.0;

        foreach ($boundaries as $boundary) {
            $start = $boundary['start'];
            $end = $boundary['end'];

            $openingBalance = self::openingBalance($start);
            $totals = self::seasonTotals($start, $end);
            $closingBalance = $openingBalance + $totals['collected_total'] - $totals['spent_total'];

            $grandSpent += $totals['spent_total'];
            $grandCollected += $totals['collected_total'];

            $seasons[] = [
                'season_start' => $start,
                'season_end' => $end,
                'spent_total' => $totals['spent_total'],
                'collected_total' => $totals['collected_total'],
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'costs' => self::paginatedCostsForSeason($start, $end, $perPage, $page),
            ];
        }

        return [
            'seasons' => $seasons,
            'grand_total' => [
                'spent_total' => $grandSpent,
                'collected_total' => $grandCollected,
                'closing_balance' => $seasons[0]['closing_balance'] ?? 0.0,
            ],
        ];
    }
}
