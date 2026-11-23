<?php

namespace App\Filament\Resources\UserPasswords\Pages;

use App\Filament\Resources\UserPasswords\UserPasswordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserPassword extends CreateRecord
{
    protected static string $resource = UserPasswordResource::class;
}
