<?php

namespace App\Filament\Resources\MachineModels\Pages;

use App\Filament\Resources\MachineModels\MachineModelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMachineModel extends CreateRecord
{
    protected static string $resource = MachineModelResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
