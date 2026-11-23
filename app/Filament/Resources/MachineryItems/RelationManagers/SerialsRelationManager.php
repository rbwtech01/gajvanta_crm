<?php

namespace App\Filament\Resources\MachineryItems\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;

class SerialsRelationManager extends RelationManager
{
    protected static string $relationship = 'serials';

    protected static ?string $title = 'Serial Numbers';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('serial_code')
                    ->label('Serial Code')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Generated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('qr')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn ($record) => route('machine.qr', [
                        'serialNumber' => $record->serial_number,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('id', 'asc');
    }
}
