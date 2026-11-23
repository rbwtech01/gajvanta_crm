<?php

namespace App\Filament\Agent\Resources\VisitingOrders\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Placeholder;


class VisitingOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('visit_date', 'desc')
            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('visit_date')
                    ->label('Visit Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('visitor_name')
                    ->label('Visitor Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created On')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),


                TextColumn::make('cooling_status')
                    ->label('Status')
                    ->state(function ($record): string {
                        return $record->cooling_until?->isFuture()
                            ? 'Active'
                            : 'Expired';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('can_create_from')
                    ->label('Can Create From')
                    ->state(function ($record): string {
                        if (! $record->cooling_until) {
                            return '-';
                        }

                        return $record->cooling_until->format('d M Y');
                    })
                    ->sortable(),

                TextColumn::make('primary_mobile')
                    ->label('Primary Mobile')
                    ->searchable(),

                TextColumn::make('secondary_mobile')
                    ->label('Secondary Mobile')
                    ->searchable()
                    ->placeholder('Not Provided'),

                TextColumn::make('pincode')
                    ->label('Pincode')
                    ->searchable(),

                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('state')
                    ->label('State')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('remark')
                    ->label('Remark')
                    ->limit(50)
                    ->placeholder('No Remark'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}