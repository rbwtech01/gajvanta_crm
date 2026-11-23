<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\User;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class AgentStateSalesMatrix extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.agent-state-sales-matrix';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 20;

    /**
     * Prepare all matrix data.
     *
     * Rows    = States
     * Columns = Agents
     * Values  = Sales / Revenue
     */
    protected function getViewData(): array
    {
        $query = $this->salesQuery();

        /*
         * Get Agent x State sales.
         */
        $sales = (clone $query)
            ->select([
                'state',
                'agent_id',
            ])
            ->selectRaw('SUM(COALESCE(price, 0)) as sales_value')
            ->groupBy('state', 'agent_id')
            ->get();

        /*
         * Get all agents that actually have sales
         * in the currently filtered dataset.
         */
        $agents = $sales
            ->pluck('agent_id')
            ->filter()
            ->unique()
            ->values();

        $agentModels = $agents->isEmpty()
            ? collect()
            : User::query()
                ->whereIn('id', $agents)
                ->get(['id', 'name']);

        /*
         * Build Agent columns.
         */
        $agentColumns = $agentModels
            ->map(fn ($agent) => [
                'id' => $agent->id,
                'name' => $agent->name,
            ])
            ->values();

        /*
         * Convert query result into:
         *
         * [
         *     state => [
         *         agent_id => sales
         *     ]
         * ]
         */
        $salesMatrix = [];

        foreach ($sales as $row) {
            $state = trim((string) $row->state);

            if ($state === '') {
                continue;
            }

            $salesMatrix[$state][$row->agent_id] =
                (float) $row->sales_value;
        }

        /*
         * Calculate State totals.
         */
        $stateTotals = [];

        foreach ($salesMatrix as $state => $agentSales) {
            $stateTotals[$state] = array_sum($agentSales);
        }

        /*
         * Sort STATES by total revenue DESC.
         *
         * Highest-revenue state appears first.
         */
        uasort(
            $salesMatrix,
            function (array $agentSalesA, array $agentSalesB): int {
                return array_sum($agentSalesB)
                    <=> array_sum($agentSalesA);
            }
        );

        /*
         * Calculate Agent totals.
         */
        $agentTotals = [];

        foreach ($agentColumns as $agent) {
            $agentTotals[$agent['id']] = 0;

            foreach ($salesMatrix as $agentSales) {
                $agentTotals[$agent['id']] +=
                    $agentSales[$agent['id']] ?? 0;
            }
        }

        /*
         * Attach total revenue to every agent.
         *
         * This makes the sorting explicit:
         *
         * Highest revenue agent
         *        ↓
         * First column
         */
        $agentColumns = $agentColumns
            ->map(function (array $agent) use ($agentTotals): array {
                $agent['total_sales'] =
                    $agentTotals[$agent['id']] ?? 0;

                return $agent;
            })
            ->sortByDesc('total_sales')
            ->values();

        /*
         * Grand total.
         */
        $grandTotal = array_sum($stateTotals);

        return [
            'agents' => $agentColumns,
            'salesMatrix' => $salesMatrix,
            'stateTotals' => $stateTotals,
            'agentTotals' => $agentTotals,
            'grandTotal' => $grandTotal,
        ];
    }

    /**
     * Base customer query with the same business exclusions
     * used by the dashboard.
     */
    protected function salesQuery(): Builder
    {
        $query = Customer::query()
            ->whereNotNull('state')
            ->where('state', '!=', '');

        /*
         * Global Agent filter.
         */
        $agentId = $this->pageFilters['agent'] ?? null;

        if (filled($agentId)) {
            $query->where('agent_id', $agentId);
        }

        /*
         * Exclude Amazon agent.
         */
        $query->whereDoesntHave('agent', function (Builder $query) {
            $query->where('name', 'Amazon');
        });

        /*
         * Exclude Amazon Store call center.
         */
        $query->whereDoesntHave(
            'agent.callCenter',
            function (Builder $query) {
                $query->where('name', 'Amazon Store');
            }
        );

        /*
         * Apply the same dashboard date filters.
         */
        return $this->applyDateFilter(
            $query,
            $this->pageFilters['period'] ?? 'this_month',
            $this->pageFilters['from'] ?? null,
            $this->pageFilters['to'] ?? null,
        );
    }

    /**
     * Keep date filtering consistent with the dashboard.
     */
    protected function applyDateFilter(
        Builder $query,
        ?string $period,
        ?string $from,
        ?string $to,
    ): Builder {
        /*
         * Custom date range has priority.
         */
        if ($from || $to) {
            return $query
                ->when(
                    $from,
                    fn (Builder $query) => $query->whereDate('booking_date', '>=', $from)
                )
                ->when(
                    $to,
                    fn (Builder $query) => $query->whereDate('booking_date', '<=', $to)
                );
        }

        switch ($period) {
            case 'today':
                $query->whereDate('booking_date', today());
                break;

            case 'yesterday':
                $query->whereDate(
                    'booking_date',
                    today()->subDay()
                );
                break;

            case 'this_week':
                $query->whereBetween('booking_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]);
                break;

            case 'last_7_days':
                $query->whereBetween('booking_date', [
                    today()->subDays(6),
                    today(),
                ]);
                break;

            case 'last_week':
                $start = now()->subWeek()->startOfWeek();
                $end = now()->subWeek()->endOfWeek();

                $query->whereBetween('booking_date', [
                    $start,
                    $end,
                ]);
                break;

            case 'last_14_days':
                $query->whereBetween('booking_date', [
                    today()->subDays(13),
                    today(),
                ]);
                break;

            case 'this_month':
                $query
                    ->whereMonth('booking_date', now()->month)
                    ->whereYear('booking_date', now()->year);
                break;

            case 'last_30_days':
                $query->whereBetween('booking_date', [
                    today()->subDays(29),
                    today(),
                ]);
                break;

            case 'last_month':
                $date = now()->subMonth();

                $query
                    ->whereMonth('booking_date', $date->month)
                    ->whereYear('booking_date', $date->year);
                break;

            case 'this_quarter':
                $query->whereBetween('booking_date', [
                    now()->startOfQuarter(),
                    now()->endOfQuarter(),
                ]);
                break;

            case 'this_year':
                $query->whereYear(
                    'booking_date',
                    now()->year
                );
                break;

            case 'all':
            case null:
            default:
                break;
        }

        return $query;
    }
}
