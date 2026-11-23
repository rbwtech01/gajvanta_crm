<?php

namespace App\Filament\Resources\UserPasswords\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserPasswordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->autocomplete('new-password')
                    ->confirmed(),

                TextInput::make('password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->autocomplete('new-password'),
            ]);
    }
}