<?php

namespace App\Filament\Widgets;

use App\Models\CallCenter;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CallCenterPerformance extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Call Center Performance';

    protected string $color = 'primary';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $period = $this->pageFilters['period'] ?? 'this_month';
        $from = $this->pageFilters['from'] ?? null;
        $to = $this->pageFilters['to'] ?? null;
        $agentId = $this->pageFilters['agent'] ?? null;

        $callCenters = CallCenter::with([
            'users' => function ($query) use ($agentId) {
                $query->when($agentId, fn ($query) => $query->whereKey($agentId));
            },
            'users.customers' => function ($query) use ($period, $from, $to) {

                if ($from || $to) {
                    $query
                        ->when($from, fn ($q) => $q->whereDate('booking_date', '>=', $from))
                        ->when($to, fn ($q) => $q->whereDate('booking_date', '<=', $to));

                    return;
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
            },
        ])->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $callCenters
                        ->map(fn ($callCenter) => $callCenter->users
                            ->sum(fn ($user) => $user->customers->sum('price')))
                        ->values()
                        ->all(),

                    'borderRadius' => 8,
                ],
            ],

            'labels' => $callCenters
                ->pluck('name')
                ->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
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
        return 'bar';
    }
}
