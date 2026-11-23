<?php

namespace App\Filament\Operations\Resources\Complaints\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ComplaintForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('complaint')
                    ->disabled(),

                Textarea::make('reply')
                    ->label('Reply')
                    ->rows(5)
                    ->required(),
            ]);
    }
}