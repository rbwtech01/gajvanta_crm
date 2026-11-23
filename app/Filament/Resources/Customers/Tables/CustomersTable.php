<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Exports\AgentCustomersExport;
use App\Models\CategoryAttachment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->whereDoesntHave('agent', function (Builder $query) {
                        $query->where('name', 'Amazon');
                    })
                    ->whereDoesntHave('agent.callCenter', function (Builder $query) {
                        $query->where('name', 'Amazon Store');
                    })
            )
            ->defaultSort('booking_date', 'desc')
            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),

                ImageColumn::make('payment_proof')
                    ->label('Payment Proof')
                    ->getStateUsing(function ($record) {
                        return $record->getFirstMediaUrl('screenshots');
                    })
                    ->defaultImageUrl('https://placehold.co/100x100/png?text=No+Image')
                    ->visibility('public')
                    ->circular()
                    ->size(50),

                TextColumn::make('booking_date')
                    ->label('Booking Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('agent.callCenter.name')
                    ->label('Call Center')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('machineModel.name')
                    ->label('Machine Model')
                    ->searchable()
                    ->sortable(),

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
                    ->money('INR'),

                TextColumn::make('advance_payment')
                    ->money('INR'),

                TextColumn::make('bill_number')
                    ->label('Bill Number')
                    ->placeholder('Not Filled')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('holdReason.reason')
                    ->label('Hold Reason')
                    ->placeholder('No Reason')
                    ->wrap(),

                TextColumn::make('hold_remark')
                    ->label('Hold Remark')
                    ->placeholder('No Remark')
                    ->wrap(),

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
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {

                        $filters = $livewire->tableFilters ?? [];

                        $agentId = $filters['agent']['value'] ?? null;
                        $bookingFilter = $filters['booking_period'] ?? [];
                        $from = $bookingFilter['from'] ?? null;
                        $until = $bookingFilter['until'] ?? null;

                        if (! $from && ! $until) {
                            [$from, $until] = match ($bookingFilter['period'] ?? null) {
                                'today' => [today()->toDateString(), today()->toDateString()],
                                'yesterday' => [
                                    today()->subDay()->toDateString(),
                                    today()->subDay()->toDateString(),
                                ],
                                'this_week' => [
                                    now()->startOfWeek()->toDateString(),
                                    now()->endOfWeek()->toDateString(),
                                ],
                                'last_7_days' => [
                                    now()->subDays(6)->toDateString(),
                                    today()->toDateString(),
                                ],
                                'this_month' => [
                                    now()->startOfMonth()->toDateString(),
                                    now()->endOfMonth()->toDateString(),
                                ],
                                'last_month' => [
                                    now()->subMonth()->startOfMonth()->toDateString(),
                                    now()->subMonth()->endOfMonth()->toDateString(),
                                ],
                                'this_quarter' => [
                                    now()->startOfQuarter()->toDateString(),
                                    now()->endOfQuarter()->toDateString(),
                                ],
                                'this_year' => [
                                    now()->startOfYear()->toDateString(),
                                    now()->endOfYear()->toDateString(),
                                ],
                                default => [null, null],
                            };
                        }

                        return Excel::download(
                            new AgentCustomersExport(
                                agentId: $agentId ? (int) $agentId : null,
                                from: $from,
                                until: $until,
                            ),
                            'customers.xlsx'
                        );
                    }),
            ])
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('agent')
                    ->relationship(
                        'agent',
                        'name',
                        fn (Builder $query) => $query
                            ->role('agent')
                            ->where('name', '!=', 'Amazon')
                            ->whereDoesntHave('callCenter', function (Builder $query) {
                                $query->where('name', 'Amazon Store');
                            })
                    )
                    ->searchable()
                    ->preload()
                    ->label('Agent'),

                // Filter::make('period')
                //     ->label('Period')
                //     ->schema([
                //         Select::make('value')
                //             ->label('Period')
                //             ->options([
                //                 'all_time' => 'All Time',
                //                 'today' => 'Today',
                //                 'yesterday' => 'Yesterday',
                //                 'this_week' => 'This Week',
                //                 'last_7_days' => 'Last 7 Days',
                //                 'this_month' => 'This Month',
                //                 'last_month' => 'Last Month',
                //                 'this_quarter' => 'This Quarter',
                //                 'this_year' => 'This Year',
                //             ])
                //             ->default('this_month')
                //             ->native(false),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {

                //         $period = $data['value'] ?? null;

                //         if (! $period || $period === 'all_time') {
                //             return $query;
                //         }

                //         return match ($period) {

                //             'today' => $query
                //                 ->whereDate('booking_date', today()),

                //             'yesterday' => $query
                //                 ->whereDate('booking_date', today()->subDay()),

                //             'this_week' => $query
                //                 ->whereBetween('booking_date', [
                //                     now()->startOfWeek(),
                //                     now()->endOfWeek(),
                //                 ]),

                //             'last_7_days' => $query
                //                 ->whereBetween('booking_date', [
                //                     now()->subDays(6)->startOfDay(),
                //                     now()->endOfDay(),
                //                 ]),

                //             'this_month' => $query
                //                 ->whereBetween('booking_date', [
                //                     now()->startOfMonth(),
                //                     now()->endOfMonth(),
                //                 ]),

                //             'last_month' => $query
                //                 ->whereBetween('booking_date', [
                //                     now()->subMonth()->startOfMonth(),
                //                     now()->subMonth()->endOfMonth(),
                //                 ]),

                //             'this_quarter' => $query
                //                 ->whereBetween('booking_date', [
                //                     now()->startOfQuarter(),
                //                     now()->endOfQuarter(),
                //                 ]),

                //             'this_year' => $query
                //                 ->whereBetween('booking_date', [
                //                     now()->startOfYear(),
                //                     now()->endOfYear(),
                //                 ]),

                //             default => $query,
                //         };
                //     }),

                // Filter::make('booking_date')
                //     ->label('Booking Date')
                //     ->schema([
                //         DatePicker::make('from')
                //             ->label('From Date'),

                //         DatePicker::make('until')
                //             ->label('To Date'),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['from'] ?? null,
                //                 fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '>=', $date)
                //             )
                //             ->when(
                //                 $data['until'] ?? null,
                //                 fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '<=', $date)
                //             );
                // }),
                Filter::make('booking_period')
                    ->label('Period')
                    ->schema([

                        Select::make('period')
                            ->label('Period')
                            ->options([
                                'all_time' => 'All Time',
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'this_week' => 'This Week',
                                'last_7_days' => 'Last 7 Days',
                                'this_month' => 'This Month',
                                'last_month' => 'Last Month',
                                'this_quarter' => 'This Quarter',
                                'this_year' => 'This Year',
                            ])
                            ->placeholder('Select a period')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $set('from', null);
                                    $set('until', null);
                                }
                            }),

                        DatePicker::make('from')
                            ->label('From Date')
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $set('period', null);
                                }
                            }),

                        DatePicker::make('until')
                            ->label('To Date')
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $set('period', null);
                                }
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {

                        // Custom date range
                        if (! empty($data['from']) || ! empty($data['until'])) {

                            return $query
                                ->when(
                                    $data['from'] ?? null,
                                    fn (Builder $query, $date) => $query->whereDate('booking_date', '>=', $date)
                                )
                                ->when(
                                    $data['until'] ?? null,
                                    fn (Builder $query, $date) => $query->whereDate('booking_date', '<=', $date)
                                );
                        }

                        // Period
                        return match ($data['period'] ?? null) {

                            'today' => $query->whereDate(
                                'booking_date',
                                today()
                            ),

                            'yesterday' => $query->whereDate(
                                'booking_date',
                                today()->subDay()
                            ),

                            'this_week' => $query->whereBetween('booking_date', [
                                now()->startOfWeek(),
                                now()->endOfWeek(),
                            ]),

                            'last_7_days' => $query->whereBetween('booking_date', [
                                now()->subDays(6)->startOfDay(),
                                now()->endOfDay(),
                            ]),

                            'this_month' => $query->whereBetween('booking_date', [
                                now()->startOfMonth(),
                                now()->endOfMonth(),
                            ]),

                            'last_month' => $query->whereBetween('booking_date', [
                                now()->subMonth()->startOfMonth(),
                                now()->subMonth()->endOfMonth(),
                            ]),

                            'this_quarter' => $query->whereBetween('booking_date', [
                                now()->startOfQuarter(),
                                now()->endOfQuarter(),
                            ]),

                            'this_year' => $query->whereBetween('booking_date', [
                                now()->startOfYear(),
                                now()->endOfYear(),
                            ]),

                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
