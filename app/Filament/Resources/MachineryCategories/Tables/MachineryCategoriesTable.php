<?php

namespace App\Filament\Resources\MachineryCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;

class MachineryCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(
            //     fn (Builder $query): Builder => $query
            //         ->where('is_active', true)
            // )

            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),
                    
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

            ])

            ->filters([
                //
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