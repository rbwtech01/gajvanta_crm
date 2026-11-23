<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ManagerRevenueTrend extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Revenue Trend';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $callCenterId = auth()->user()->call_center_id;

        $period = $this->pageFilters['period'] ?? 'this_month';

        $query = Customer::query()
            ->whereHas('agent', function ($query) use ($callCenterId) {
                $query->where('call_center_id', $callCenterId);
            })
            ->whereNotNull('booking_date');

        match ($period) {
            'today' => $query->whereDate('booking_date', today()),

            'yesterday' => $query->whereDate(
                'booking_date',
                today()->subDay()
            ),

            'this_week' => $query->whereBetween('booking_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]),

            'this_month' => $query
                ->whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year),

            'this_year' => $query->whereYear(
                'booking_date',
                now()->year
            ),

            default => null,
        };

        $revenue = $query
            ->selectRaw('booking_date, SUM(price) as total_revenue')
            ->groupBy('booking_date')
            ->orderBy('booking_date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenue
                        ->pluck('total_revenue')
                        ->map(fn ($value) => (float) $value)
                        ->toArray(),
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $revenue
                ->pluck('booking_date')
                ->map(fn ($date) => date('d M', strtotime($date)))
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}