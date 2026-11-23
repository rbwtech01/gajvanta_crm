<?php

namespace App\Filament\Supervisor\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupervisorOrderStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Pending',
                Customer::query()
                    ->where('status', 'created')
                    ->whereDate('booking_date', '>=', '2026-09-01')
                    ->count()
            )
                ->description('Waiting for supervisor review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(
                'Hold',
                Customer::query()
                    ->where('status', 'supervisor_hold')
                    ->whereDate('booking_date', '>=', '2026-09-01')
                    ->count()
            )
                ->description('Orders currently on hold')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('danger'),

            Stat::make(
                'Confirmed',
                Customer::query()
                    ->where('status', 'supervisor_confirmed')
                    ->whereDate('booking_date', '>=', '2026-09-01')
                    ->count()
            )
                ->description('Approved by supervisor')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}