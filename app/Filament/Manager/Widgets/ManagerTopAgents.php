<?php

namespace App\Filament\Manager\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ManagerTopAgents extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '🏆 Top Performing Agents';

    public function table(Table $table): Table
    {
        return $table
            ->searchable(false)

            ->query(function () {
                return User::query()
                    ->where('call_center_id', auth()->user()->call_center_id)
                    ->where('id', '!=', auth()->id())

                    ->withCount([
                        'customers as customers_count' => function ($query) {
                            $this->applyPeriodFilter(
                                $query,
                                $this->pageFilters['period'] ?? 'this_month'
                            );
                        },
                    ])

                    ->withSum([
                        'customers as revenue' => function ($query) {
                            $this->applyPeriodFilter(
                                $query,
                                $this->pageFilters['period'] ?? 'this_month'
                            );
                        },
                    ], 'price')

                    ->withCount([
                        'customers as created_count' => function ($query) {
                            $this->applyPeriodFilter(
                                $query,
                                $this->pageFilters['period'] ?? 'this_month'
                            );

                            $query->where('status', 'created');
                        },

                        'customers as hold_count' => function ($query) {
                            $this->applyPeriodFilter(
                                $query,
                                $this->pageFilters['period'] ?? 'this_month'
                            );

                            $query->where('status', 'hold');
                        },

                        'customers as dispatched_count' => function ($query) {
                            $this->applyPeriodFilter(
                                $query,
                                $this->pageFilters['period'] ?? 'this_month'
                            );

                            $query->where('status', 'dispatched');
                        },

                        'customers as delivered_count' => function ($query) {
                            $this->applyPeriodFilter(
                                $query,
                                $this->pageFilters['period'] ?? 'this_month'
                            );

                            $query->where('status', 'delivered');
                        },
                    ])

                    ->with('callCenter')

                    ->orderByDesc('revenue');
            })

            ->columns([
                TextColumn::make('rank')
                    ->label('Rank')
                    ->state(
                        fn ($record, $livewire) =>
                            $livewire->getTableRecords()->search($record) + 1
                    ),

                TextColumn::make('name')
                    ->label('Agent'),

                TextColumn::make('callCenter.name')
                    ->label('Call Center')
                    ->badge(),

                TextColumn::make('customers_count')
                    ->label('Customers')
                    ->badge(),

                TextColumn::make('revenue')
                    ->label('Revenue')
                    ->money('INR'),

                TextColumn::make('created_count')
                    ->label('Created')
                    ->badge(),

                TextColumn::make('hold_count')
                    ->label('Hold')
                    ->badge(),

                TextColumn::make('dispatched_count')
                    ->label('Dispatched')
                    ->badge(),

                TextColumn::make('delivered_count')
                    ->label('Delivered')
                    ->badge(),
            ])

            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    private function applyPeriodFilter(
        Builder $query,
        ?string $period
    ): void {
        match ($period) {
            'today' => $query->whereDate(
                'booking_date',
                today()
            ),

            'yesterday' => $query->whereDate(
                'booking_date',
                today()->subDay()
            ),

            'this_week' => $query->whereBetween(
                'booking_date',
                [
                    now()->startOfWeek()->toDateString(),
                    now()->endOfWeek()->toDateString(),
                ]
            ),

            'this_month' => $query
                ->whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year),

            'this_year' => $query->whereYear(
                'booking_date',
                now()->year
            ),

            default => null,
        };
    }
}