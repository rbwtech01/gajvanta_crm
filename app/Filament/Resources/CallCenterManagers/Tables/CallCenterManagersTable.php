<?php

namespace App\Filament\Resources\CallCenterManagers\Tables;

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

class CallCenterManagersTable
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
                    ->label('Manager Code')
                    ->searchable(),

                TextColumn::make('callCenter.name')
                    ->label('Call Center')
                    ->searchable(),

                TextColumn::make('name')
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

                IconColumn::make('is_active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

            ])
            ->defaultSort('id', 'desc')

            ->filters([
                TrashedFilter::make(),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}