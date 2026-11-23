<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueTrend extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Revenue Trend';

    protected static ?int $sort = 3;

    protected string $color = 'success';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '350px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $period = $this->pageFilters['period'] ?? 'this_month';
        $from = $this->pageFilters['from'] ?? null;
        $to = $this->pageFilters['to'] ?? null;

        $query = Customer::query()->when(
            $this->pageFilters['agent'] ?? null,
            fn ($query, $agentId) => $query->where('agent_id', $agentId)
        );

        if ($from || $to) {
            $query
                ->when($from, fn ($q) => $q->whereDate('booking_date', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('booking_date', '<=', $to));
        } else {
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
        }

        $sales = $query
            ->selectRaw('DATE(booking_date) as date, SUM(price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date');

        $labels = [];
        $data = [];

        foreach ($sales as $date => $revenue) {
            $labels[] = Carbon::parse($date)->format('d M');
            $data[] = (float) $revenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₹)',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],

            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],

            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
