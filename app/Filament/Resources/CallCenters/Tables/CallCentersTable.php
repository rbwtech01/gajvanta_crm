<?php

namespace App\Filament\Resources\CallCenters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Illuminate\Database\Eloquent\Builder;

class CallCentersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Call Center')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Address')
                    ->searchable()
                    ->default("-"),

                TextColumn::make('city')
                    ->label('District')
                    ->searchable()
                    ->default("-"),

                TextColumn::make('state')
                    ->searchable()
                    ->default("-"),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable(),

            ])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(function (Builder $query): void {
                if (! request()->boolean('active_call_centers')) {
                    return;
                }

                $period = request('period', 'this_month');
                $from = request('from');
                $to = request('to');
                $agentId = request('agent_id');

                $query->whereHas('users.customers', function (Builder $customerQuery) use ($period, $from, $to, $agentId): void {
                    $customerQuery->when(
                        $agentId,
                        fn (Builder $query) => $query->where('agent_id', $agentId)
                    );

                    if ($from || $to) {
                        $customerQuery
                            ->when($from, fn (Builder $query) => $query->whereDate('booking_date', '>=', $from))
                            ->when($to, fn (Builder $query) => $query->whereDate('booking_date', '<=', $to));

                        return;
                    }

                    match ($period) {
                        'today' => $customerQuery->whereDate('booking_date', today()),
                        'yesterday' => $customerQuery->whereDate('booking_date', today()->subDay()),
                        'this_week' => $customerQuery->whereBetween('booking_date', [now()->startOfWeek(), now()->endOfWeek()]),
                        'last_7_days' => $customerQuery->whereBetween('booking_date', [today()->subDays(6), today()]),
                        'this_month' => $customerQuery->whereMonth('booking_date', now()->month)->whereYear('booking_date', now()->year),
                        'last_month' => $customerQuery->whereMonth('booking_date', now()->subMonth()->month)->whereYear('booking_date', now()->subMonth()->year),
                        'this_quarter' => $customerQuery->whereBetween('booking_date', [now()->startOfQuarter(), now()->endOfQuarter()]),
                        'this_year' => $customerQuery->whereYear('booking_date', now()->year),
                        default => null,
                    };
                });
            })
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // DeleteAction::make(),
                // RestoreAction::make(),
                // ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ]);
    }
}
