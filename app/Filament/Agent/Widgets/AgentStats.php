<?php

namespace App\Filament\Agent\Widgets;

use App\Filament\Agent\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgentStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected function customerQuery()
    {
        $query = Customer::query()
            ->where('user_id', auth()->id());

        $period = $this->pageFilters['period'] ?? 'all';
        $from = $this->pageFilters['from'] ?? null;
        $to = $this->pageFilters['to'] ?? null;

        if ($from || $to) {
            $query
                ->when($from, fn ($q) => $q->whereDate('booking_date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('booking_date', '<=', $to));

            return $query;
        }

        switch ($period) {
            case 'today':
                $query->whereDate('booking_date', today());
                break;

            case 'yesterday':
                $query->whereDate('booking_date', today()->subDay());
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
                $query->whereMonth('booking_date', now()->month)
                    ->whereYear('booking_date', now()->year);
                break;

            case 'last_month':
                $date = now()->subMonth();

                $query->whereMonth('booking_date', $date->month)
                    ->whereYear('booking_date', $date->year);
                break;

            case 'this_quarter':
                $query->whereBetween('booking_date', [
                    now()->startOfQuarter(),
                    now()->endOfQuarter(),
                ]);
                break;

            case 'this_year':
                $query->whereYear('booking_date', now()->year);
                break;
        }

        return $query;
    }

    protected function getStats(): array
    {
        return [

            Stat::make(
                'Total Customers',
                (clone $this->customerQuery())->count()
            )
                ->description('Customers')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('danger')
                ->url(fn () => $this->customerUrl()),

            Stat::make(
                'Unconfirmed',
                (clone $this->customerQuery())
                    ->whereIn('status', [
                        'created',
                        'supervisor_hold',
                    ])
                    ->count()
            )
                ->description('Unconfirmed orders')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(fn () => $this->customerUrl('created')),

            Stat::make(
                'Processing',
                (clone $this->customerQuery())
                    ->where('status', 'processing')
                    ->count()
            )
                ->description('Processing orders')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning')
                ->url(fn () => $this->customerUrl('processing')),

            Stat::make(
                'Confirmed',
                (clone $this->customerQuery())
                    ->whereIn('status', [
                        'confirmed',
                        'supervisor_confirmed',
                    ])
                    ->count()
            )
                ->description('Confirmed orders')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(fn () => $this->customerUrl('confirmed')),

            Stat::make(
                'Hold',
                (clone $this->customerQuery())
                    ->where('status', 'hold')
                    ->count()
            )
                ->description('Hold orders')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('info')
                ->url(fn () => $this->customerUrl('hold')),

            Stat::make(
                'Pack',
                (clone $this->customerQuery())
                    ->where('status', 'packed')
                    ->count()
            )
                ->description('Packed orders')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('success')
                ->url(fn () => $this->customerUrl('packed')),

            Stat::make(
                'Dispatched',
                (clone $this->customerQuery())
                    ->where('status', 'dispatched')
                    ->count()
            )
                ->description('Pending delivery')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(fn () => $this->customerUrl('dispatched')),

            Stat::make(
                'Delivered',
                (clone $this->customerQuery())
                    ->where('status', 'delivered')
                    ->count()
            )
                ->description('Delivered orders')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(fn () => $this->customerUrl('delivered')),

            Stat::make(
                'Total Revenue',
                '₹'.number_format(
                    (clone $this->customerQuery())->sum('price')
                )
            )
                ->description('Overall sales')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url(fn () => $this->customerUrl()),

            Stat::make(
                'Advance Payment',
                '₹'.number_format(
                    (clone $this->customerQuery())->sum('advance_payment')
                )
            )
                ->description('Received advance')
                ->descriptionIcon('heroicon-m-currency-rupee')
                ->color('info')
                ->url(fn () => $this->customerUrl()),

        ];
    }

    protected function customerUrl(?string $status = null): string
    {
        return CustomerResource::getUrl('index', array_filter([
            'period' => $this->pageFilters['period'] ?? 'all',
            'status' => $status,
        ], fn ($value) => filled($value)));
    }
}
