<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentCustomers extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 8;

    protected static ?string $heading = 'Recent Customers';

    public static function canView(): bool
    {
        return false;
    }

    public function table(Table $table): Table  
    {
        return $table
            ->query(
                Customer::query()->latest()
            )

            ->defaultPaginationPageOption(5)

            ->columns([
                TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->searchable(),

                TextColumn::make('product_name')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->badge(),

                TextColumn::make('price')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'delivered',
                        'warning' => 'undelivered',
                        'danger' => 'hold',
                    ]),

                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])

            ->filters([

                SelectFilter::make('status')
                    ->options([
                        'delivered' => 'Delivered',
                        'undelivered' => 'Undelivered',
                        'hold' => 'Hold',
                    ]),

                Filter::make('today')
                    ->query(fn (Builder $query) =>
                        $query->whereDate('created_at', today())
                    ),

                Filter::make('this_week')
                    ->query(fn (Builder $query) =>
                        $query->whereBetween(
                            'created_at',
                            [now()->startOfWeek(), now()->endOfWeek()]
                        )
                    ),

                Filter::make('this_month')
                    ->query(fn (Builder $query) =>
                        $query->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year)
                    ),
            ]);
    }
}