<?php

namespace App\Filament\Resources\Users\Tables;

use App\Exports\AgentsExport;
use App\Models\AgentTarget;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query
                    ->where('is_active', true)
                    ->where('name', '!=', 'Amazon')
                    ->whereDoesntHave('callCenter', function (Builder $query) {
                        $query->where('name', 'Amazon Store');
                    });
            })
            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->state(
                        static fn ($record, $livewire) => $livewire->getTableRecords()->search($record) + 1
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

                TextColumn::make('salary')
                    ->label('Salary (Monthly)')
                    ->money('INR'),

                TextColumn::make('monthly_salary')
                    ->label('Monthly Salary')
                    ->money('INR'),

                TextColumn::make('monthly_revenue')
                    ->label('Revenue')
                    ->state(fn ($record) => Customer::query()
                        ->where('agent_id', $record->id)
                        ->whereMonth('booking_date', now()->month)
                        ->whereYear('booking_date', now()->year)
                        ->sum('price'))
                    ->money('INR'),

                IconColumn::make('performance')
                    ->label('Performance')
                    ->state(function ($record) {

                        $target = 300000;

                        $sales = Customer::query()
                            ->where('agent_id', $record->id)
                            ->whereMonth('booking_date', now()->month)
                            ->whereYear('booking_date', now()->year)
                            ->sum('price');

                        $expected = ($target / now()->daysInMonth) * now()->day;

                        return match (true) {
                            $sales >= $target => 'green',
                            $sales >= $expected => 'yellow',
                            default => 'red',
                        };
                    })
                    ->icon(fn (string $state) => match ($state) {
                        'green' => 'heroicon-m-check-circle',
                        'yellow' => 'heroicon-m-exclamation-circle',
                        'red' => 'heroicon-m-x-circle',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'green' => 'success',
                        'yellow' => 'warning',
                        'red' => 'danger',
                    })
                    ->tooltip(fn (string $state) => match ($state) {
                        'green' => 'Monthly Target Achieved',
                        'yellow' => 'On Track',
                        'red' => 'Behind Target',
                    }),

                TextColumn::make('monthly_target')
                    ->label('Target')
                    ->state(function ($record) {
                        return AgentTarget::query()
                            ->where('agent_id', $record->id)
                            ->where('month', now()->month)
                            ->where('year', now()->year)
                            ->value('target_amount');
                    })
                    ->money('INR')
                    ->placeholder('Not Set'),

                IconColumn::make('is_active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false)
            ->defaultSort('id', 'desc')
            ->headerActions([
                Action::make('calendar')
                    ->label('View Orders')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary')
                    ->url(fn () => route('filament.admin.pages.order-calendar')),

                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {

                        return Excel::download(
                            new AgentsExport(
                                request('period'),
                                request('from'),
                                request('to'),
                            ),
                            'agents.xlsx'
                        );

                    }),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('viewOrders')
                    ->label('View Orders')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary')
                    ->url(fn ($record) => route(
                        'filament.admin.pages.order-calendar',
                        [
                            'agent' => $record->id,
                        ]
                    )),

                Action::make('addTarget')
                    ->label('Add Target')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Select::make('month')
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
                            ->required(),

                        TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->default(now()->year)
                            ->required(),

                        TextInput::make('target_amount')
                            ->label('Monthly Target')
                            ->numeric()
                            ->prefix('₹')
                            ->required()
                            ->minValue(0),
                    ])
                    ->action(function (array $data, $record): void {
                        AgentTarget::updateOrCreate(
                            [
                                'agent_id' => $record->id,
                                'month' => $data['month'],
                                'year' => $data['year'],
                            ],
                            [
                                'target_amount' => $data['target_amount'],
                            ],
                        );
                    }),
                Action::make('setSalary')
                    ->label('Update Salary')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        TextInput::make('salary')
                            ->label('Salary (Monthly)')
                            ->prefix('₹')
                            ->numeric()
                            ->minValue(0)
                            ->step('0.01')
                            ->default(fn ($record) => $record->salary)
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'salary' => $data['salary'],
                        ]);
                    }),
                EditAction::make(),
                // DeleteAction::make(),
                // RestoreAction::make(),
                // ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
