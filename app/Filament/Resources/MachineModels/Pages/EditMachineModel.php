<?php

namespace App\Filament\Resources\MachineModels\Pages;

use App\Filament\Resources\MachineModels\MachineModelResource;
use Filament\Resources\Pages\EditRecord;

class EditMachineModel extends EditRecord
{
    protected static string $resource = MachineModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
