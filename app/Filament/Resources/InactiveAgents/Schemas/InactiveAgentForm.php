<?php

namespace App\Filament\Resources\InactiveAgents\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InactiveAgentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_active')
                    ->label('Active Status')
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->required(),
            ]);
    }
}