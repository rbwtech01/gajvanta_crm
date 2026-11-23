<?php

namespace App\Filament\Resources\UserPasswords\Pages;

use App\Filament\Resources\UserPasswords\UserPasswordResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserPassword extends EditRecord
{
    protected static string $resource = UserPasswordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
