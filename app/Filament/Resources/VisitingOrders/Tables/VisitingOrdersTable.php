<?php

namespace App\Filament\Resources\VisitingOrders\Tables;

use App\Exports\VisitingOrdersExport;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class VisitingOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(
                            new VisitingOrdersExport(),
                            'visiting-orders.xlsx'
                        );
                    }),
            ])

            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('agent.callCenter.name')
                ->label('Call Center')
                ->searchable()
                ->sortable()
                ->placeholder('Not Assigned'),

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

                TextColumn::make('primary_mobile')
                    ->label('Primary Mobile')
                    ->searchable(),

                TextColumn::make('secondary_mobile')
                    ->label('Secondary Mobile')
                    ->searchable()
                    ->placeholder('Not Provided'),

                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('state')
                    ->label('State')
                    ->searchable()
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
                        'Expired' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('cooling_until')
                    ->label('Can Create From')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->placeholder('Available'),

                TextColumn::make('remark')
                    ->label('Remark')
                    ->limit(50)
                    ->placeholder('No Remark'),
            ])

            // No Edit/Delete actions
            ->recordUrl(null)
            ->recordAction(null);
    }
}