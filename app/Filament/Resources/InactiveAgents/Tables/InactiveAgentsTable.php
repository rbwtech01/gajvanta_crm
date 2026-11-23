<?php

namespace App\Filament\Resources\InactiveAgents\Tables;

use App\Exports\AgentsExport;
use App\Models\Customer;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

use Maatwebsite\Excel\Facades\Excel;

class InactiveAgentsTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([

                TextColumn::make('sr_no')
                    ->label('#')
                    ->state(
                        static fn ($record, $livewire) =>
                            $livewire->getTableRecords()->search($record) + 1
                    ),

                TextColumn::make('employee_code')
                    ->label('Agent Code')
                    ->searchable(),

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('callCenter.name')
                    ->label('Call Center')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('admin_password')
                    ->label('Password')
                    ->icon('heroicon-o-clipboard')
                    ->iconPosition('after')
                    ->copyable()
                    ->copyMessage('Password copied!')
                    ->copyMessageDuration(1500),

                TextColumn::make('phone')
                    ->searchable(),

                /*
                |--------------------------------------------------------------------------
                | Monthly Revenue
                |--------------------------------------------------------------------------
                */

                TextColumn::make('monthly_revenue')
                    ->label('Revenue')
                    ->state(
                        fn ($record) => Customer::query()
                            ->where('agent_id', $record->id)
                            ->whereMonth(
                                'booking_date',
                                now()->month
                            )
                            ->whereYear(
                                'booking_date',
                                now()->year
                            )
                            ->sum('price')
                    )
                    ->money('INR'),

                /*
                |--------------------------------------------------------------------------
                | Performance
                |--------------------------------------------------------------------------
                */

                IconColumn::make('performance')
                    ->label('Performance')
                    ->state(function ($record) {

                        $target = 300000;

                        $sales = Customer::query()
                            ->where('agent_id', $record->id)
                            ->whereMonth(
                                'booking_date',
                                now()->month
                            )
                            ->whereYear(
                                'booking_date',
                                now()->year
                            )
                            ->sum('price');

                        $expected =
                            ($target / now()->daysInMonth)
                            * now()->day;

                        return match (true) {

                            $sales >= $target => 'green',

                            $sales >= $expected => 'yellow',

                            default => 'red',
                        };
                    })
                    ->icon(
                        fn (string $state) => match ($state) {

                            'green' =>
                                'heroicon-m-check-circle',

                            'yellow' =>
                                'heroicon-m-exclamation-circle',

                            'red' =>
                                'heroicon-m-x-circle',
                        }
                    )
                    ->color(
                        fn (string $state) => match ($state) {

                            'green' => 'success',

                            'yellow' => 'warning',

                            'red' => 'danger',
                        }
                    )
                    ->tooltip(
                        fn (string $state) => match ($state) {

                            'green' =>
                                'Monthly Target Achieved',

                            'yellow' =>
                                'On Track',

                            'red' =>
                                'Behind Target',
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | Active Status
                |--------------------------------------------------------------------------
                */

                IconColumn::make('is_active')
                    ->label('Inactive')
                    ->boolean(),

                /*
                |--------------------------------------------------------------------------
                | Created At
                |--------------------------------------------------------------------------
                */

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])

            ->recordUrl(null)

            ->defaultSort('id', 'desc')

            /*
            |--------------------------------------------------------------------------
            | Header Actions
            |--------------------------------------------------------------------------
            */

            ->headerActions([

                // Action::make('calendar')
                //     ->label('View Orders')
                //     ->icon('heroicon-o-calendar-days')
                //     ->color('primary')
                //     ->url(
                //         fn () => route(
                //             'filament.admin.pages.order-calendar'
                //         )
                //     ),

                // Action::make('export_excel')
                //     ->label('Export Excel')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->color('success')
                //     ->action(function () {

                //         return Excel::download(
                //             new AgentsExport(
                //                 request('period'),
                //                 request('from'),
                //                 request('to'),
                //             ),
                //             'inactive-agents.xlsx'
                //         );
                // }),
            ])

            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */

            ->filters([
                // TrashedFilter::make(),
            ])

            /*
            |--------------------------------------------------------------------------
            | Record Actions
            |--------------------------------------------------------------------------
            */

            ->recordActions([

                Action::make('viewOrders')
                    ->label('View Orders')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary')
                    ->url(fn ($record) => route(
                        'filament.admin.pages.order-calendar',
                        ['agent' => $record->id]
                    )),

                EditAction::make(),

                // DeleteAction::make(),

                // RestoreAction::make(),

                // ForceDeleteAction::make(),
            ])

            /*
            |--------------------------------------------------------------------------
            | Bulk Actions
            |--------------------------------------------------------------------------
            */

            ->toolbarActions([

                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),

            ]);
    }
}