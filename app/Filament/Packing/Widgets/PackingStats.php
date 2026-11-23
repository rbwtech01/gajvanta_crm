<?php

namespace App\Filament\Packing\Widgets;

use App\Filament\Packing\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PackingStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $pendingOrders = Customer::query()
            ->whereDate('booking_date', '>=', '2026-09-01')
            ->where('status', 'confirmed')
            ->count();

        $packedOrders = Customer::query()
            ->whereDate('booking_date', '>=', '2026-09-01')
            ->where('status', 'packed')
            ->count();

        return [
            Stat::make('Pending Orders', number_format($pendingOrders))
                ->description('Orders waiting for packing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'confirmed'])),

            Stat::make('Packed Orders', number_format($packedOrders))
                ->description('Orders packed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'packed'])),
        ];
    }
}