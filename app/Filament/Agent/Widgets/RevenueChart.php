<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue (Last 30 Days)';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    protected string $color = 'success';

    protected function getData(): array
    {
        $agentId = auth()->id();

        $labels = [];
        $revenue = [];

        $revenues = Customer::query()
            ->where('user_id', $agentId)
            ->whereDate('booking_date', '>=', now()->subDays(29))
            ->selectRaw('DATE(booking_date) as date, SUM(price) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date');

        foreach (range(29, 0) as $day) {

            $date = now()->subDays($day);

            $labels[] = $date->format('d M');

            $revenue[] = (float) ($revenues[$date->format('Y-m-d')] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₹)',
                    'data' => $revenue,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],

            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
