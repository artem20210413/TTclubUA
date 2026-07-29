<?php

namespace App\Eloquent;

use App\Models\Costs;
use App\Models\Finance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class FinanceStatisticsEloquent
{
    /**
     * Boundary for one specific season, identified by the calendar year its start month falls in
     * (e.g. year=2025 with season_start_month=9 → season "2025-09-01" .. "2026-08-31").
     * `end` is null when this is the current, in-progress season.
     *
     * @return array{start: Carbon, end: ?Carbon}
     */
    public static function seasonBoundaryForYear(?int $year): array
    {
        $startMonth = config('club.season_start_month', 9);
        $now = now();
        $currentSeasonStartYear = $now->month < $startMonth ? $now->year - 1 : $now->year;

        $year ??= $currentSeasonStartYear;

        $start = Carbon::create($year, $startMonth, 1)->startOfDay();
        $end = $year === $currentSeasonStartYear ? null : $start->copy()->addYear();

        return ['start' => $start, 'end' => $end];
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
     * @return array{season_start: Carbon, season_end: ?Carbon, spent_total: float, collected_total: float, opening_balance: float, closing_balance: float, costs: LengthAwarePaginator}
     */
    public static function buildSeasonStatistics(?int $year, int $perPage, int $page): array
    {
        $boundary = self::seasonBoundaryForYear($year);
        $start = $boundary['start'];
        $end = $boundary['end'];

        $openingBalance = self::openingBalance($start);
        $totals = self::seasonTotals($start, $end);
        $closingBalance = $openingBalance + $totals['collected_total'] - $totals['spent_total'];

        return [
            'season_start' => $start,
            'season_end' => $end,
            'spent_total' => $totals['spent_total'],
            'collected_total' => $totals['collected_total'],
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'costs' => self::paginatedCostsForSeason($start, $end, $perPage, $page),
        ];
    }
}
