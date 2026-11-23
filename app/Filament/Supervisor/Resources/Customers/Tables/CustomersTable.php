<?php

namespace App\Filament\Supervisor\Resources\Customers\Tables;

use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Supervisor sees ONLY orders waiting for review.
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereIn('status', [
                    'created',
                    'supervisor_hold',
                ])
                ->whereDate('booking_date', '>=', '2026-09-01')
            )
            ->defaultSort('created_at', 'desc')

            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('agent.callCenter.name')
                    ->label('Call Center')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->label('Mobile')
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable(),

                TextColumn::make('state')
                    ->label('State')
                    ->searchable(),

                TextColumn::make('district')
                    ->label('District')
                    ->searchable(),

                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),

                /*
                 * Supervisor review action.
                 *
                 * Current records are "created".
                 * Supervisor can change them to either:
                 * - supervisor_hold
                 * - supervisor_confirm
                 */
                SelectColumn::make('status')
                    ->label('Supervisor Decision')
                    ->options([
                        'created' => 'Select Decision',
                        'supervisor_hold' => 'Supervisor Hold',
                        'supervisor_confirmed' => 'Supervisor Confirmed',
                    ])
                    ->selectablePlaceholder(false),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])

            // No filters for the review-only page.
            ->filters([])

            // No Edit button.
            ->recordActions([])

            // No delete / restore / bulk actions.
            ->toolbarActions([]);
    }
}