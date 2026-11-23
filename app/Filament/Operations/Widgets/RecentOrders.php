<?php

namespace App\Filament\Operations\Widgets;

use App\Models\Customer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrders extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()
                    ->with([
                        'category',
                        'machineModel',
                        'agent',
                    ])
                    ->whereDate('booking_date', '>=', '2026-09-01')
                    ->whereIn('status', ['created', 'hold'])
                    ->latest('booking_date')
            )
            ->columns([
                            TextColumn::make('sr_no')
                                ->label('#')
                                ->rowIndex(),

                            TextColumn::make('customer_name')
                                ->label('Customer'),

                            TextColumn::make('mobile')
                                ->label('Mobile'),

                            TextColumn::make('category.name')
                                ->label('Category')
                                ->badge(),

                            TextColumn::make('machineModel.name')
                                ->label('Machine'),

                            TextColumn::make('price')
                                ->label('Price')
                                ->money('INR'),

                            TextColumn::make('advance_payment')
                                ->label('Advance')
                                ->money('INR'),

                            TextColumn::make('agent.name')
                                ->label('Sales Executive'),

                            TextColumn::make('booking_date')
                                ->label('Booking Date')
                                ->date('d/m/Y'),

                            TextColumn::make('status')
                                ->badge(),
                        ])
            ->paginated([5, 10]);
    }

    public static function canView(): bool
    {
        return false;
    }
}
