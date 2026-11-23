<?php

namespace App\Filament\Resources\Amazons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\AmazonOrdersExport;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;

class AmazonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query->where(function (Builder $query) {
                    $query
                        ->whereHas('agent', function (Builder $query) {
                            $query->where('name', 'Amazon');
                        })
                        ->orWhereHas('agent.callCenter', function (Builder $query) {
                            $query->where('name', 'Amazon Store');
                        });
                })
            )
            ->defaultSort('booking_date', 'desc')
            
            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('booking_date')
                    ->label('Booking Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('agent.callCenter.name')
                    ->label('Call Center')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('machineModel.name')
                    ->label('Machine Model')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->money('INR'),

                TextColumn::make('advance_payment')
                    ->money('INR'),

                TextColumn::make('bill_number')
                    ->label('Bill Number')
                    ->placeholder('Not Filled')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('state'),

                TextColumn::make('district'),

                TextColumn::make('shipment.tracking_id')
                    ->label('Tracking ID')
                    ->placeholder('Not Generated')
                    ->searchable(),

                TextColumn::make('shipment.dispatch_date')
                    ->label('Dispatch Date')
                    ->date('d/m/Y')
                    ->placeholder('Not Dispatched')
                    ->sortable(),
            ])

            ->recordUrl(null)
            ->recordAction(null)

            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $month = $livewire->tableFilters['month']['value'] ?? null;

                        return Excel::download(
                            new AmazonOrdersExport(
                                $month ? (int) $month : null
                            ),
                            'amazon-orders.xlsx'
                        );
                    }),
            ])

            ->filters([
                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ])
                     ->default(now()->month)
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $query, $month) => $query->whereMonth('booking_date', $month)
                        );
                    })
                    ->searchable(),
            ])

            ->recordActions([
                // EditAction::make(),
            ])

            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
