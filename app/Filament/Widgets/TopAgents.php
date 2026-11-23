<?php

namespace App\Filament\Widgets;

use App\Models\AgentTarget;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopAgents extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected static ?string $heading = '🏆 Top Performing Agents';

    protected int|string|array $columnSpan = 'full';

    protected function getAgentQuery(): Builder
    {
        $period = $this->pageFilters['period'] ?? 'this_month';
        $from = $this->pageFilters['from'] ?? null;
        $to = $this->pageFilters['to'] ?? null;
        $agentId = $this->pageFilters['agent'] ?? null;

        return User::role('agent')
            ->where('is_active', true)
            ->where('name', '!=', 'Amazon')
            ->whereDoesntHave('callCenter', function (Builder $query) {
                $query->where('name', 'Amazon Store');
            })
            ->when($agentId, fn (Builder $query) => $query->whereKey($agentId))
            ->with('callCenter')

            ->withCount([

                'customers' => function (Builder $query) use ($period, $from, $to) {
                    $this->applyDateFilter($query, $period, $from, $to);
                },

                'customers as created_count' => function (Builder $query) use ($period, $from, $to) {

                    $this->applyDateFilter($query, $period, $from, $to);

                    $query->whereIn('status', [
                        'created',
                        'supervisor_hold',
                    ]);
                },

                'customers as hold_count' => function (Builder $query) use ($period, $from, $to) {

                    $this->applyDateFilter($query, $period, $from, $to);

                    $query->where('status', 'hold');
                },

                'customers as confirmed_count' => function (Builder $query) use ($period, $from, $to) {

                    $this->applyDateFilter($query, $period, $from, $to);

                    $query->where('status', 'confirmed');
                },

                'customers as processing_count' => function (Builder $query) use ($period, $from, $to) {

                    $this->applyDateFilter($query, $period, $from, $to);

                    $query->where('status', 'processing');
                },

                'customers as packed_count' => function (Builder $query) use ($period, $from, $to) {

                    $this->applyDateFilter($query, $period, $from, $to);

                    $query->where('status', 'packed');
                },

                'customers as dispatched_count' => function (Builder $query) use ($period, $from, $to) {

                    $this->applyDateFilter($query, $period, $from, $to);

                    $query->where('status', 'dispatched');
                },

                'customers as delivered_count' => function (Builder $query) use ($period, $from, $to) {

                    $this->applyDateFilter($query, $period, $from, $to);

                    $query->where('status', 'delivered');
                },

            ])
            ->withSum([
                'customers' => function (Builder $query) use ($period, $from, $to) {

                    $this->applyDateFilter($query, $period, $from, $to);
                },
            ], 'price')
            ->orderByDesc('customers_sum_price');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getAgentQuery())

            ->defaultSort('customers_sum_price', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->state(function ($record, $livewire) {

                        $rank = $livewire->getTableRecords()->search($record) + 1;

                        return match ($rank) {
                            1 => "🥇 {$rank}",
                            2 => "🥈 {$rank}",
                            3 => "🥉 {$rank}",
                            default => "#{$rank}",
                        };
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->label('Agent')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('callCenter.name')
                    ->label('Call Center')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customers_count')
                    ->label('Customers')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customers_sum_price')
                    ->label('Revenue')
                    ->default(0)
                    ->money('INR', divideBy: 1)
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('achieved_till_today')
                    ->label('Achieved Till Today')
                    ->state(fn ($record) => $this->getAchievedTillToday($record))
                    ->money('INR', divideBy: 1)
                    ->default(0)
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($record) => $this->isBehindTarget($record) ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('target_till_today')
                    ->label('Target Till Today')
                    ->state(fn ($record) => $this->getTargetTillToday($record))
                    ->money('INR', divideBy: 1)
                    ->placeholder('Not Set')
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($record) => $this->isBehindTarget($record) ? 'danger' : null),

                Tables\Columns\TextColumn::make('daily_target')
                    ->label('Daily Target')
                    ->state(function ($record) {
                        $monthlyTarget = $this->getMonthlyTarget($record);

                        if (! $monthlyTarget) {
                            return null;
                        }

                        return $monthlyTarget / now()->daysInMonth;
                    })
                    ->money('INR', divideBy: 1)
                    ->placeholder('Not Set')
                    ->alignEnd()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_count')
                    ->label('Unconfirmed')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('hold_count')
                    ->label('Hold')
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('confirmed_count')
                    ->label('Confirmed')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('processing_count')
                    ->label('Processing')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('packed_count')
                    ->label('Packed')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('dispatched_count')
                    ->label('Dispatched')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('delivered_count')
                    ->label('Delivered')
                    ->badge()
                    ->color('success'),
            ])

            ->recordActions([
                Action::make('view_orders')
                    ->label('View Orders')
                    ->icon('heroicon-o-calendar-days')
                    ->url(fn ($record) => route('filament.admin.pages.order-calendar', [
                        'agent' => $record->getKey(),
                    ])),
            ])

            ->filters([

                SelectFilter::make('call_center')
                    ->label('Call Center')
                    ->relationship('callCenter', 'name')
                    ->searchable()
                    ->preload(),

                // SelectFilter::make('status')
                //     ->options([
                //         'created' => 'Created',
                //         'hold' => 'Hold',
                //         'dispatched' => 'Dispatched',
                //         'delivered' => 'Delivered',
                //     ])
                //     ->query(function (Builder $query, array $data) {

                //         if (blank($data['value'])) {
                //             return $query;
                //         }

                //         return $query->whereHas('customers', function (Builder $query) use ($data) {
                //             $query->where('status', $data['value']);
                //         });
                // }),

            ])

            // Optional: highlight the whole row red when behind target
            // ->recordClasses(fn ($record) => $this->isBehindTarget($record)
            //     ? 'bg-danger-50 dark:bg-danger-500/10'
            //     : null)

            ->striped()

            ->paginated([500])

            ->defaultPaginationPageOption(500)

            ->emptyStateHeading('No performance data available')

            ->emptyStateDescription('Agents will appear here after customer records are assigned.');
    }

    protected function getMonthlyTarget($record): ?float
    {
        return AgentTarget::query()
            ->where('agent_id', $record->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->value('target_amount');
    }

    protected function getAchievedTillToday($record): float
    {
        return (float) $record->customers()
            ->whereDate('booking_date', '>=', now()->startOfMonth())
            ->whereDate('booking_date', '<=', today())
            ->sum('price');
    }

    protected function getTargetTillToday($record): ?float
    {
        $monthlyTarget = $this->getMonthlyTarget($record);

        if (! $monthlyTarget) {
            return null;
        }

        return ($monthlyTarget / now()->daysInMonth) * now()->day;
    }

    protected function isBehindTarget($record): bool
    {
        $target = $this->getTargetTillToday($record);

        // No target set = nothing to compare against, so don't flag it
        if ($target === null) {
            return false;
        }

        return $this->getAchievedTillToday($record) < $target;
    }

    protected function applyDateFilter(
        Builder $query,
        string $period,
        ?string $from,
        ?string $to,
    ): void {

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
    }

    protected function periodLabel(): string
    {
        return match ($this->pageFilters['period'] ?? 'this_month') {

            'today' => 'Today',

            'yesterday' => 'Yesterday',

            'this_week' => 'This Week',

            'last_7_days' => 'Last 7 Days',

            'this_month' => 'This Month',

            'last_month' => 'Last Month',

            'this_quarter' => 'This Quarter',

            'this_year' => 'This Year',

            default => 'This Month',
        };
    }
}