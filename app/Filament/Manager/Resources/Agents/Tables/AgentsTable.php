<?php

namespace App\Filament\Manager\Resources\Agents\Tables;

use App\Filament\Manager\Resources\Agents\AgentResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AgentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->state(
                        static fn ($record, $livewire) => $livewire->getTableRecords()->search($record) + 1
                    ),

                TextColumn::make('employee_code')
                    ->label('Employee Code')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Phone'),
            ])

            ->filters([])

            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->where('call_center_id', auth()->user()->call_center_id)
                    ->where('id', '!=', auth()->id())
            )

            ->recordActions([
                            Action::make('customers')
                                ->label('Customers')
                                ->icon('heroicon-o-users')
                                ->url(
                                    fn ($record) => AgentResource::getUrl(
                                        'customers',
                                        ['record' => $record]
                                    )
                                ),
                        ])

            ->toolbarActions([
                BulkActionGroup::make([
                                DeleteBulkAction::make(),
                ]),
            ]);
    }
}
