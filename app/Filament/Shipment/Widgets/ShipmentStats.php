<?php

namespace App\Filament\Shipment\Widgets;

use App\Filament\Shipment\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShipmentStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $pendingShipment = Customer::query()
            ->whereDate('booking_date', '>=', '2026-09-01')
            ->whereIn('status', ['packed'])
            ->count();

        $dispatched = Customer::query()
            ->whereDate('booking_date', '>=', '2026-09-01')
            ->where('status', 'dispatched')
            ->count();

        $delivered = Customer::query()
            ->whereDate('booking_date', '>=', '2026-09-01')
            ->where('status', 'delivered')
            ->count();

        return [

            Stat::make(
                'Pending Shipment',
                number_format($pendingShipment)
            )
                ->description('Orders waiting for shipment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'packed'])),

            Stat::make(
                'Dispatched',
                number_format($dispatched)
            )
                ->description('Orders dispatched')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'dispatched'])),

            Stat::make(
                'Delivered',
                number_format($delivered)
            )
                ->description('Orders delivered')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'delivered'])),
        ];
    }
}