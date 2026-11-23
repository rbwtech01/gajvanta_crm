<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('call_center_id')
                    ->relationship('callCenter', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'This email is already registered.',
                    ]),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('salary')
                    ->label('Salary (Monthly)')
                    ->helperText('Used to calculate the agent’s daily salary in the Meta Ads report.')
                    ->prefix('₹')
                    ->numeric()
                    ->minValue(0)
                    ->step('0.01')
                    ->default(10000)
                    ->required(),
                TextInput::make('monthly_salary')
                    ->label('Monthly Salary')
                    ->prefix('₹')
                    ->numeric()
                    ->minValue(0)
                    ->step('0.01')
                    ->default(15000)
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
