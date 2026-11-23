<?php

namespace App\Filament\Packing\Resources\Customers\Tables;

use App\Models\CategoryAttachment;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->whereDate('booking_date', '>=', '2026-09-01')
                    ->where('status', 'confirmed');
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

                TextColumn::make('attachments')
                    ->label('Attachments')
                    ->state(function ($record) {
                        if (blank($record->attachments)) {
                            return [];
                        }

                        return CategoryAttachment::whereIn('id', $record->attachments)
                            ->pluck('title')
                            ->toArray();
                    })
                    ->badge(),

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

                TextColumn::make('status')
                    ->badge(),
            ])

            ->filters([
                // TrashedFilter::make(),
            ])

            ->recordActions([
                EditAction::make()
                    ->label('Pack Order')
                    ->icon('heroicon-o-cube'),
            ])

            ->toolbarActions([

            ]);
    }
}
