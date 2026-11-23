<?php

namespace App\Filament\Operations\Widgets;

use App\Filament\Operations\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class OrderStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Common filter for ALL cards
        $baseQuery = Customer::query()
            ->whereDate('booking_date', '>=', '2026-09-01')
            ->whereDoesntHave('agent', function (Builder $query) {
                $query->where('name', 'Amazon');
            })
            ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                $query->where('name', 'Amazon Store');
            });

        // All Orders - only valid order statuses
        $allOrders = (clone $baseQuery)
            ->whereIn('status', [
                'created',
                'supervisor_hold',
                'supervisor_confirmed',
                'hold',
                'processing',
                'confirmed',
                'packed',
                'dispatched',
                'delivered',
            ])
            ->count();

        // Pending
        $pendingOrders = (clone $baseQuery)
            ->whereIn('status', [
                'supervisor_confirmed',
                'supervisor_hold',
                'created',
            ])
            ->count();

        // Hold
        $holdOrders = (clone $baseQuery)
            ->where('status', 'hold')
            ->count();

        // Processed
        $processedOrders = (clone $baseQuery)
            ->where('status', 'processing')
            ->count();

        // Confirmed
        $confirmedOrders = (clone $baseQuery)
            ->where('status', 'confirmed')
            ->count();

        // Packed
        $packedOrders = (clone $baseQuery)
            ->where('status', 'packed')
            ->count();

        // Dispatched
        $dispatchedOrders = (clone $baseQuery)
            ->where('status', 'dispatched')
            ->count();

        // Delivered
        $deliveredOrders = (clone $baseQuery)
            ->where('status', 'delivered')
            ->count();

        return [
            Stat::make('All Orders', number_format($allOrders))
                ->description('All Orders')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning')
                ->url(fn () => CustomerResource::getUrl('index')),

            Stat::make('Pending', number_format($pendingOrders))
                ->description('Pending Order')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'created'])),

            Stat::make('Hold', number_format($holdOrders))
                ->description('Hold Order')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'hold'])),

            Stat::make('Processed', number_format($processedOrders))
                ->description('Processed Order')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'processing'])),

            Stat::make('Confirmed', number_format($confirmedOrders))
                ->description('Confirmed Order')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'confirmed'])),

            Stat::make('Packed', number_format($packedOrders))
                ->description('Packed Order')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'packed'])),

            Stat::make('Dispatched', number_format($dispatchedOrders))
                ->description('Dispatched Order')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'dispatched'])),

            Stat::make('Delivered', number_format($deliveredOrders))
                ->description('Delivered Order')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(fn () => CustomerResource::getUrl('index', ['status' => 'delivered'])),
        ];
    }
}