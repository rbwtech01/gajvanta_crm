<?php

namespace App\Filament\Resources\MachineryItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class MachineryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('machinery_category_id')
                    ->label('Machinery Category')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('is_active', true),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->label('Model Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('model_code')
                    ->label('Product Short Code')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                TextInput::make('model_tag')
                    ->label('Model Tag')
                    ->nullable()
                    ->maxLength(255),

                TextInput::make('dealer_price')
                    ->label('Dealer Price')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₹')
                    ->required(),

                TextInput::make('mrp')
                    ->label('MRP')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₹')
                    ->nullable(),

                TextInput::make('mop')
                    ->label('MOP')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₹')
                    ->nullable(),

                TextInput::make('tax_rate')
                    ->label('Tax Rate')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->nullable(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}