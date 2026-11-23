<?php

namespace App\Filament\Operations\Resources\Customers\Tables;

use App\Exports\OrderConfirmExport;
use App\Models\CategoryAttachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

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
                    ->whereIn('status', [
                        'created',
                        'supervisor_hold',
                        'supervisor_confirmed',
                        'hold',
                        'processing',
                        'confirmed',
                        'dispatched',
                        'delivered',
                        'packed'
                    ])
                    ->when(
                        request()->query('status'),
                        fn (Builder $query, string $status) => $query->where('status', $status)
                    );
            })

            ->defaultSort('id', 'desc')

            ->columns([

                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(function ($record) {
                        return $record->shipment
                            ? null
                            : '🆕 New Order';
                    }),

                TextColumn::make('mobile')
                    ->label('Mobile')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->searchable(),

                TextColumn::make('machineModel.name')
                    ->label('Machine')
                    ->searchable(),

                TextColumn::make('attachments')
                    ->label('Attachments')
                    ->state(function ($record) {
                        if (blank($record->attachments)) {
                            return [];
                        }

                        return CategoryAttachment::whereIn(
                            'id',
                            $record->attachments
                        )
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
                    ->label('Sales Executive')
                    ->searchable(),

                TextColumn::make('booking_date')
                    ->label('Booking Date')
                    ->date('d/m/Y')
                    ->sortable(),

                
                TextColumn::make('bill_number')
                    ->label('Bill Number')
                    ->placeholder('Not Filled')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'created' => 'warning',
                        'hold' => 'danger',
                        'processing' => 'success',
                        'packed' => 'info',
                        'dispatched' => 'primary',
                        'in_transit' => 'gray',
                        'delivered' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('bill_number')
                    ->label('Bill Number')
                    ->placeholder('N/A')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('hold_remark')
                    ->label('Remark')
                    ->placeholder(
                        fn ($record) => $record->status === 'processing'
                            ? 'N/A'
                            : 'No remark'
                    )
                    ->limit(30)
                    ->tooltip(
                        fn ($record) => $record->hold_remark
                    ),

                TextColumn::make('delivery_date')
                    ->label('On Demand')
                    ->date('d/m/Y')
                    ->placeholder('Not Scheduled')
                    ->sortable(),


                TextColumn::make('district')
                    ->hidden(),

                TextColumn::make('state')
                    ->hidden(),

                TextColumn::make('pincode')
                    ->hidden(),

                TextColumn::make('address')
                    ->limit(40)
                    ->hidden(),
            ])

            ->filters([

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'created' => 'Created',
                        'hold' => 'Hold',
                        'processing' => 'Processing',
                        'confirmed' => 'Confirmed',
                    ]),

                /*
                 * ONE date filter
                 *
                 * Preset date OR specific date.
                 * Only one can be active at a time.
                 */
                Filter::make('order_date')
                    ->label('Order Date')
                    ->form([
                        // Period
                        Select::make('date_range')
                            ->label('Period')
                            ->options([
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'this_week' => 'This Week',
                                'this_month' => 'This Month',
                                'this_year' => 'This Year',
                            ])
                            ->placeholder('Select period')
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if (filled($state)) {
                                    // Selecting a period clears custom date range
                                    $set('from_date', null);
                                    $set('to_date', null);
                                }
                            }),

                        // Custom Date Range
                        DatePicker::make('from_date')
                            ->label('From Date')
                            ->placeholder('Start date')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if (filled($state)) {
                                    // Selecting custom range clears period
                                    $set('date_range', null);
                                }
                            }),

                        DatePicker::make('to_date')
                            ->label('To Date')
                            ->placeholder('End date')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if (filled($state)) {
                                    // Selecting custom range clears period
                                    $set('date_range', null);
                                }
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {

                        // Custom date range
                        if (
                            filled($data['from_date'] ?? null) ||
                            filled($data['to_date'] ?? null)
                        ) {
                            return $query
                                ->when(
                                    $data['from_date'] ?? null,
                                    fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '>=', $date)
                                )
                                ->when(
                                    $data['to_date'] ?? null,
                                    fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '<=', $date)
                                );
                        }

                        // Period
                        return match ($data['date_range'] ?? null) {

                            'today' => $query->whereDate(
                                'booking_date',
                                Carbon::today()
                            ),

                            'yesterday' => $query->whereDate(
                                'booking_date',
                                Carbon::yesterday()
                            ),

                            'this_week' => $query->whereBetween(
                                'booking_date',
                                [
                                    Carbon::now()->startOfWeek(),
                                    Carbon::now()->endOfWeek(),
                                ]
                            ),

                            'this_month' => $query->whereBetween(
                                'booking_date',
                                [
                                    Carbon::now()->startOfMonth(),
                                    Carbon::now()->endOfMonth(),
                                ]
                            ),

                            'this_year' => $query->whereBetween(
                                'booking_date',
                                [
                                    Carbon::now()->startOfYear(),
                                    Carbon::now()->endOfYear(),
                                ]
                            ),

                            default => $query,
                        };
                    }),
            ])

            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {

                        $query = $livewire->getFilteredTableQuery();

                        return Excel::download(
                            new OrderConfirmExport($query),
                            'order-confirm.xlsx'
                        );
                    }),
            ])

            ->recordActions([
                EditAction::make()
                    ->label('Manage Order'),

                Action::make('generate_bill')
                    ->label('Bill')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function ($record) {

                        $billNo = filled($record->bill_number)
                            ? $record->bill_number
                            : 'RBW/'.random_int(1000, 999999);

                        $pdf = Pdf::loadView('pdf.invoice', [
                            'customer' => $record,

                            'attachments' => CategoryAttachment::whereIn(
                                'id',
                                $record->attachments ?? []
                            )
                                ->pluck('title')
                                ->toArray(),

                            'balance' => $record->price - $record->advance_payment,

                            'invoiceNo' => $billNo,
                        ]);

                        return response()->streamDownload(
                            fn () => print ($pdf->output()),
                            'Bill-'.Str::slug($record->customer_name).'.pdf',
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
