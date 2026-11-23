<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Models\Category;
use Closure;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) =>
                        $set('slug', Str::slug($state))
                    ),

              
            TextInput::make('slug')
                ->required()
                ->rules([
                    function (): Closure {
                        return function (string $attribute, $value, Closure $fail) {

                            $deletedCategory = Category::onlyTrashed()
                                ->where('slug', $value)
                                ->first();

                            if ($deletedCategory) {
                                $fail('A category with this slug already exists but is soft deleted. Please restore the deleted category instead of creating a new one.');
                            }
                        };
                    },
                ]),

                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}