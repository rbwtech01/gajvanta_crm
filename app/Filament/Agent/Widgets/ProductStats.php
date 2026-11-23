<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $topProduct = Customer::query()
            ->selectRaw('product_name, COUNT(*) as total')
            ->groupBy('product_name')
            ->orderByDesc('total')
            ->first();

        return [
            Stat::make('Products Sold', Customer::count())
                ->description('Total orders')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make(
                'Top Product',
                $topProduct?->product_name ?? 'N/A'
            )
                ->description(
                    ($topProduct?->total ?? 0) . ' orders'
                )
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),

            Stat::make(
                'Unique Products',
                Customer::distinct('product_name')->count('product_name')
            )
                ->description('Different products sold')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make(
                'Average Order',
                '₹' . number_format(Customer::avg('price') ?? 0, 0)
            )
                ->description('Average selling price')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }

    public static function canView(): bool
    {
        return false;
    }
}