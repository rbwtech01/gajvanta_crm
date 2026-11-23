<?php

namespace App\Filament\Resources\UserPasswords\Pages;

use App\Filament\Resources\UserPasswords\UserPasswordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserPasswords extends ListRecords
{
    protected static string $resource = UserPasswordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
