<?php

namespace App\Filament\Resources\MachineryItems\Tables;

use App\Exports\MachinerySerialsExport;
use App\Services\MachinerySerialService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class MachineryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Model Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('model_code')
                    ->label('Model Code')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),

                TextColumn::make('model_tag')
                    ->label('Model Tag')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),

                TextColumn::make('dealer_price')
                    ->label('Dealer Price')
                    ->money('INR')
                    ->sortable()
                    ->placeholder('N/A'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])

            ->filters([])

            ->recordActions([

                Action::make('addStock')
                    ->label('Add Stock')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->modalHeading(
                        fn ($record) => "Add Stock - {$record->name}"
                    )
                    ->modalSubmitActionLabel('Generate Stock')
                    ->action(function ($record, array $data): void {

                        $quantity = (int) $data['quantity'];

                        app(MachinerySerialService::class)
                            ->generate($record, $quantity);

                        Notification::make()
                            ->success()
                            ->title('Stock added successfully')
                            ->body("{$quantity} serial numbers generated.")
                            ->send();
                    }),

                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->schema([
                        TextInput::make('limit')
                            ->label('Number of records')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required()
                            ->placeholder('e.g. 20')
                            ->helperText(
                                fn ($record) => 'Available records: '.$record->serials()->count()
                            ),
                    ])
                    ->modalHeading(
                        fn ($record) => "Download Serial Numbers - {$record->name}"
                    )
                    ->modalDescription(
                        'Enter the number of serial numbers you want to export.'
                    )
                    ->modalSubmitActionLabel('Download Excel')
                    ->action(function ($record, array $data) {

                        $limit = (int) $data['limit'];

                        $available = $record->serials()->count();

                        if ($limit > $available) {

                            Notification::make()
                                ->danger()
                                ->title('Not enough records')
                                ->body(
                                    "You requested {$limit} records, ".
                                    "but only {$available} records are available."
                                )
                                ->send();

                            return;
                        }

                        return Excel::download(
                            new MachinerySerialsExport(
                                $record->id,
                                $limit
                            ),
                            "machinery-serials-{$record->model_code}.xlsx"
                        );
                    }),

                EditAction::make(),

                DeleteAction::make(),

            ]);
    }
}
