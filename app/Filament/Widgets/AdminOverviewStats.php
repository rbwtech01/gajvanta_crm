<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\DashboardDetails;
use App\Models\CallCenter;
use App\Models\Customer;
use App\Models\User;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class AdminOverviewStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $customerQuery = fn () => clone $this->customerQuery();

        return [

            /*
            |--------------------------------------------------------------------------
            | Total Revenue
            |--------------------------------------------------------------------------
            | No redirect
            */

            Stat::make(
                'Total Revenue',
                '₹'.number_format(
                    $customerQuery()
                        ->whereDoesntHave('agent', function (Builder $query) {
                            $query->where('name', 'Amazon');
                        })
                        ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                            $query->where('name', 'Amazon Store');
                        })
                        ->sum('price')
                )
            )
                ->description('Revenue for selected period')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            /*
            |--------------------------------------------------------------------------
            | Active Agents
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Active Agents',

                User::role('Agent')
                    ->where('name', '!=', 'Amazon')
                    ->when(
                        $this->pageFilters['agent'] ?? null,
                        fn (Builder $query, $agentId) => $query->whereKey($agentId)
                    )
                    ->whereHas('customers', function (Builder $query) {
                        $this->applyDateFilter(
                            $query,
                            $this->pageFilters['period'] ?? 'this_month',
                            $this->pageFilters['from'] ?? null,
                            $this->pageFilters['to'] ?? null,
                        );
                    })
                    ->count()
            )
                ->description('Agents with bookings')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'agents',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Total Customers
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Total Customers',
                $customerQuery()
                    ->whereDoesntHave('agent', function (Builder $query) {
                        $query->where('name', 'Amazon');
                    })
                    ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                        $query->where('name', 'Amazon Store');
                    })
                    ->count()
            )
                ->description('Customers for selected period')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'customers',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Unconfirmed
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Unconfirmed',
                $customerQuery()
                    ->whereDoesntHave('agent', function (Builder $query) {
                        $query->where('name', 'Amazon');
                    })
                    ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                        $query->where('name', 'Amazon Store');
                    })
                    ->whereIn('status', [
                        'created',
                        'supervisor_hold',
                        'supervisor_confirmed',
                    ])
                    ->count()
            )
                ->description('Unconfirmed orders')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('gray')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'unconfirmed',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Hold
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Hold',
                $customerQuery()
                    ->where('status', 'hold')
                    ->count()
            )
                ->description('Orders on hold')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('warning')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'hold',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Confirmed
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Confirmed',
                $customerQuery()
                    ->where('status', 'confirmed')
                    ->count()
            )
                ->description('Orders on confirmed')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('success')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'confirmed',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Processing
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Processing',
                $customerQuery()
                    ->where('status', 'processing')
                    ->count()
            )
                ->description('Orders on processing')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('warning')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'processing',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Packed
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Packed',
                $customerQuery()
                    ->where('status', 'packed')
                    ->count()
            )
                ->description('Orders packed')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('success')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'packed',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Dispatched
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Dispatched',
                $customerQuery()
                    ->where('status', 'dispatched')
                    ->count()
            )
                ->description('Orders dispatched')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'dispatched',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Delivered
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Delivered',
                $customerQuery()
                    ->where('status', 'delivered')
                    ->count()
            )
                ->description('Delivered orders')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'delivered',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Active Call Centers
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Active Call Centers',

                CallCenter::whereHas('users.customers', function (Builder $query) {
                    $query->when(
                        $this->pageFilters['agent'] ?? null,
                        fn (Builder $query, $agentId) => $query->where('agent_id', $agentId)
                    );

                    $this->applyDateFilter(
                        $query,
                        $this->pageFilters['period'] ?? 'this_month',
                        $this->pageFilters['from'] ?? null,
                        $this->pageFilters['to'] ?? null,
                    );
                })->count()
            )
                ->description('Centers with bookings')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('gray')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'call_centers',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            /*
            |--------------------------------------------------------------------------
            | Average Order Value
            |--------------------------------------------------------------------------
            | No redirect
            */

            Stat::make(
                'Average Order Value',
                '₹'.number_format(
                    round($customerQuery()->avg('price') ?? 0)
                )
            )
                ->description('Average booking value')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            /*
            |--------------------------------------------------------------------------
            | Missing Bill Number
            |--------------------------------------------------------------------------
            */

            Stat::make(
                'Missing Bill Number',
                $customerQuery()
                    ->whereDoesntHave('agent', function (Builder $query) {
                        $query->where('name', 'Amazon');
                    })
                    ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                        $query->where('name', 'Amazon Store');
                    })
                    ->whereNull('bill_number')
                    ->count()
            )
                ->description('Orders without bill number')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'missing_bill',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            Stat::make(
                'States with sales',
                $customerQuery()
                    ->whereDoesntHave('agent', function (Builder $query) {
                        $query->where('name', 'Amazon');
                    })
                    ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                        $query->where('name', 'Amazon Store');
                    })
                    ->whereNotNull('state')
                    ->where('state', '!=', '')
                    ->distinct('state')
                    ->count('state')
            )
                ->description('States with sales')
                ->descriptionIcon('heroicon-m-map')
                ->color('info')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'states',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),

            Stat::make(
                'Districts with sales',
                $customerQuery()
                    ->whereDoesntHave('agent', function (Builder $query) {
                        $query->where('name', 'Amazon');
                    })
                    ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                        $query->where('name', 'Amazon Store');
                    })
                    ->whereNotNull('district')
                    ->where('district', '!=', '')
                    ->distinct('district')
                    ->count('district')
            )
                ->description('Districts with sales')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('success')
                ->url(fn () => DashboardDetails::getUrl([
                    'type' => 'districts',
                    'period' => $this->pageFilters['period'] ?? 'this_month',
                    'from' => $this->pageFilters['from'] ?? null,
                    'to' => $this->pageFilters['to'] ?? null,
                    'agent_id' => $this->pageFilters['agent'] ?? null,
                ])),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Customer Query
    |--------------------------------------------------------------------------
    */

    protected function customerQuery(): Builder
    {
        $period = $this->pageFilters['period'] ?? 'this_month';

        $from = $this->pageFilters['from'] ?? null;

        $to = $this->pageFilters['to'] ?? null;

        return $this->applyDateFilter(
            Customer::query()->when(
                $this->pageFilters['agent'] ?? null,
                fn (Builder $query, $agentId) => $query->where('agent_id', $agentId)
            ),
            $period,
            $from,
            $to,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Date Filter
    |--------------------------------------------------------------------------
    */

    protected function applyDateFilter(
        Builder $query,
        string $period,
        ?string $from,
        ?string $to,
    ): Builder {

        /*
         * Custom date range
         */
        if ($from || $to) {
            $query
                ->when(
                    $from,
                    fn (Builder $q) => $q->whereDate('booking_date', '>=', $from)
                )
                ->when(
                    $to,
                    fn (Builder $q) => $q->whereDate('booking_date', '<=', $to)
                );

            return $query;
        }

        switch ($period) {

            case 'today':

                $query->whereDate(
                    'booking_date',
                    today()
                );

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

            case 'this_month':

                $query
                    ->whereMonth(
                        'booking_date',
                        now()->month
                    )
                    ->whereYear(
                        'booking_date',
                        now()->year
                    );

                break;

            case 'last_month':

                $date = now()->subMonth();

                $query
                    ->whereMonth(
                        'booking_date',
                        $date->month
                    )
                    ->whereYear(
                        'booking_date',
                        $date->year
                    );

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
        }

        return $query;
    }
}
