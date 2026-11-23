<?php

namespace App\Filament\Shipment\Resources\Customers\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->whereDoesntHave('agent', function (Builder $query) {
                        $query->where('name', 'Amazon');
                    })
                    ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                        $query->where('name', 'Amazon Store');
                    });
            })
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->with([
                        'category',
                        'machineModel',
                        'agent',
                        'shipment.courier',
                    ])
                    ->whereDate('booking_date', '>=', '2026-09-01')
                    ->orderByDesc('booking_date')
                    ->orderByDesc('id');
            })

            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->label('Mobile')
                    ->searchable(),

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
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('bill_number')
                    ->label('Bill Number')
                    ->sortable(),

                // Shipment Details
                TextColumn::make('shipment.courier.name')
                    ->label('Courier')
                    ->placeholder('Not Assigned'),

                TextColumn::make('shipment.tracking_id')
                    ->label('Tracking ID')
                    ->placeholder('Not Available')
                    ->searchable(),

                TextColumn::make('shipment.dispatch_date')
                    ->label('Dispatch Date')
                    ->date('d/m/Y')
                    ->placeholder('Not Dispatched')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'gray',
                        'processing' => 'warning',
                        'packed' => 'info',
                        'dispatched' => 'primary',
                        'delivered' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
            ])

            ->recordActions([
                EditAction::make()
                    ->label('Manage Shipment'),
            ]);
    }
}