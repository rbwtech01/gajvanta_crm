<?php

namespace App\Filament\Widgets;

use App\Exports\AgentsWithoutOrders72HoursExport;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class AgentsWithoutOrders72Hours extends TableWidget
{
    protected static ?string $heading = 'Agents Without Orders - Last 72 Hours';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $cutoff = now()->subHours(72);

        return $table
            ->query(
                fn (): Builder => User::query()
                    ->role('Agent')
                    ->where('is_active', true)

                    // Exclude Amazon agent
                    ->where('name', '!=', 'Amazon')

                    ->whereNotExists(function ($query) use ($cutoff) {
                        $query
                            ->selectRaw('1')
                            ->from('customers')
                            ->whereColumn(
                                'customers.agent_id',
                                'users.id'
                            )
                            ->where(
                                'customers.created_at',
                                '>=',
                                $cutoff
                            );
                    })

                    ->withMax('customers', 'created_at')
            )

            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('callCenter.name')
                    ->label('Call Center')
                    ->searchable(),

                TextColumn::make('customers_max_created_at')
                    ->label('Last Order')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Last Order')
                    ->state(function (User $record): string {
                        $lastOrder = $record->customers_max_created_at;

                        if (! $lastOrder) {
                            return 'No orders yet';
                        }

                        return \Carbon\Carbon::parse($lastOrder)
                            ->diffForHumans([
                                'parts' => 2,
                                'join' => true,
                                'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                            ]);
                    })
                    ->badge(),
            ])

            ->filters([
                SelectFilter::make('call_center_id')
                    ->label('Call Center')
                    ->relationship(
                        'callCenter',
                        'name',
                        fn (Builder $query) => $query
                            ->where('name', '!=', 'Amazon Store')
                    )
                    ->searchable()
                    ->preload(),
            ])

            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $callCenterId =
                            $this->tableFilters['call_center_id']['value']
                            ?? null;

                        return Excel::download(
                            new AgentsWithoutOrders72HoursExport(
                                $callCenterId
                                    ? (int) $callCenterId
                                    : null
                            ),
                            'agents-without-orders-72-hours.xlsx'
                        );
                    }),
            ])

            ->defaultSort('name')
            ->paginated(false);
    }
}