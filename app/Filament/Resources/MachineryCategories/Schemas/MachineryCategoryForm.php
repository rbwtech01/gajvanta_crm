<?php

namespace App\Filament\Resources\MachineryCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MachineryCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Category Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->hidden()
                    ->dehydrated()
                    ->default(fn () => null),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}