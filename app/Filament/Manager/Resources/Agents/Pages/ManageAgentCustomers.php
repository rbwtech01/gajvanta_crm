<?php

namespace App\Filament\Manager\Resources\Agents\Pages;

use App\Filament\Manager\Resources\Agents\AgentResource;
use App\Models\CategoryAttachment;
use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManageAgentCustomers extends ManageRelatedRecords
{
    protected static string $resource = AgentResource::class;

    protected static string $relationship = 'customers';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $title = 'Customers';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('customer_name')

            ->columns([
                TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('customer_name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->label('Mobile')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),

                TextColumn::make('machineModel.name')
                    ->label('Machine Model')
                    ->searchable(),

                TextColumn::make('booking_date')
                    ->label('Booking Date')
                    ->date('d/m/Y')
                    ->sortable(),

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

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('price')
                    ->label('Price'),

                TextColumn::make('advance_payment')
                    ->label('Advance Payment'),

                TextColumn::make('address')
                    ->label('Address')
                    ->wrap(),

                TextColumn::make('pincode')
                    ->label('Pincode'),

                TextColumn::make('district')
                    ->label('District'),

                TextColumn::make('state')
                    ->label('State'),
            ])

            ->filters([
                SelectFilter::make('period')
                    ->label('Date')
                    ->options([
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        'this_week' => 'This Week',
                        'this_month' => 'This Month',
                        'this_year' => 'This Year',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'today' => $query->whereDate(
                                'booking_date',
                                today()
                            ),

                            'yesterday' => $query->whereDate(
                                'booking_date',
                                today()->subDay()
                            ),

                            'this_week' => $query->whereBetween(
                                'booking_date',
                                [
                                    now()->startOfWeek()->toDateString(),
                                    now()->endOfWeek()->toDateString(),
                                ]
                            ),

                            'this_month' => $query
                                ->whereMonth('booking_date', now()->month)
                                ->whereYear('booking_date', now()->year),

                            'this_year' => $query->whereYear(
                                'booking_date',
                                now()->year
                            ),

                            default => $query,
                        };
                    }),
            ])

            // IMPORTANT: no Edit/Delete/Dissociate actions
            ->recordActions([])

            // IMPORTANT: no bulk Delete/Dissociate actions
            ->toolbarActions([])

            // No Create/Associate buttons
            ->headerActions([])

            ->defaultSort('booking_date', 'desc');
    }
}
