<?php

namespace App\Filament\Resources\MachineModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MachineModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->label('Machine Model Name')
                    ->required()
                    ->maxLength(255)
                    ->live(),

                // TextInput::make('slug')
                //     ->required()
                //     ->maxLength(255)
                //     ->unique(ignoreRecord: true),


                Toggle::make('status')
                    ->label('Active')
                    ->default(true),
            ])
            ->columns(2);
    }
}