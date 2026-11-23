<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Customer;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ManagerStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $callCenterId = auth()->user()->call_center_id;

        // Global dashboard filter
        $period = $this->pageFilters['period'] ?? 'this_month';

        /*
        |--------------------------------------------------------------------------
        | Base customer query
        |--------------------------------------------------------------------------
        */

        $customerQuery = Customer::query()
            ->whereHas('agent', function ($query) use ($callCenterId) {
                $query->where('call_center_id', $callCenterId);
            });

        /*
        |--------------------------------------------------------------------------
        | Apply global period filter
        |--------------------------------------------------------------------------
        */

        $this->applyPeriodFilter($customerQuery, $period);

        /*
        |--------------------------------------------------------------------------
        | Total Customers
        |--------------------------------------------------------------------------
        */

        $totalCustomers = (clone $customerQuery)->count();

        /*
        |--------------------------------------------------------------------------
        | Total Revenue
        |--------------------------------------------------------------------------
        */

        $totalRevenue = (clone $customerQuery)->sum('price');

        /*
        |--------------------------------------------------------------------------
        | Status Counts
        |--------------------------------------------------------------------------
        */

        $created = (clone $customerQuery)
            ->where('status', 'created')
            ->count();

        $hold = (clone $customerQuery)
            ->where('status', 'hold')
            ->count();

        $dispatched = (clone $customerQuery)
            ->where('status', 'dispatched')
            ->count();

        $delivered = (clone $customerQuery)
            ->where('status', 'delivered')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Active Agents
        |--------------------------------------------------------------------------
        */

        $activeAgents = User::query()
            ->where('call_center_id', $callCenterId)
            ->where('id', '!=', auth()->id())
            ->whereHas('customers', function ($query) use ($period) {
                $this->applyPeriodFilter($query, $period);
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Active Call Centers
        |--------------------------------------------------------------------------
        */

        $activeCallCenters = $totalCustomers > 0 ? 1 : 0;

        /*
        |--------------------------------------------------------------------------
        | Average Order Value
        |--------------------------------------------------------------------------
        */

        $averageOrderValue = $totalCustomers > 0
            ? $totalRevenue / $totalCustomers
            : 0;

        return [

            Stat::make(
                'Total Revenue',
                '₹' . number_format($totalRevenue, 0)
            )
                ->description('Revenue for selected period')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(
                'Active Agents',
                $activeAgents
            )
                ->description('Agents with bookings')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make(
                'Total Customers',
                $totalCustomers
            )
                ->description('Customers for selected period')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make(
                'Created',
                $created
            )
                ->description('Created orders')
                ->descriptionIcon('heroicon-m-plus-circle'),

            Stat::make(
                'Hold',
                $hold
            )
                ->description('Orders on hold')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('warning'),

            Stat::make(
                'Dispatched',
                $dispatched
            )
                ->description('Orders dispatched')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make(
                'Delivered',
                $delivered
            )
                ->description('Delivered orders')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(
                'Active Call Centers',
                $activeCallCenters
            )
                ->description('Centers with bookings')
                ->descriptionIcon('heroicon-m-building-office-2'),

            Stat::make(
                'Average Order Value',
                '₹' . number_format($averageOrderValue, 0)
            )
                ->description('Average booking value')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    }

    private function applyPeriodFilter($query, ?string $period): void
    {
        match ($period) {

            'today' => $query->whereDate(
                'booking_date',
                today()
            ),

            'yesterday' => $query->whereDate(
                'booking_date',
                today()->subDay()
            ),

            'this_week' => $query->whereBetween(
                'booking_date',
                [
                    now()->startOfWeek()->toDateString(),
                    now()->endOfWeek()->toDateString(),
                ]
            ),

            'this_month' => $query
                ->whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year),

            'this_year' => $query->whereYear(
                'booking_date',
                now()->year
            ),

            default => null,
        };
    }
}