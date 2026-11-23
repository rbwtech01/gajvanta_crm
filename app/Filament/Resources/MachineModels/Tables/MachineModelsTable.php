<?php

namespace App\Filament\Resources\MachineModels\Tables;

use App\Models\Customer;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MachineModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('category');
            })
            ->columnManager(false)
            ->columns([
                TextColumn::make('sr_no')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Machine Model')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('status')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make()
                    ->before(function ($record, $action) {

                        if (Customer::where('machine_model_id', $record->id)->exists()) {

                            Notification::make()
                                ->danger()
                                ->title('Cannot Delete')
                                ->body('This machine model is already assigned to one or more customers.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }
}
