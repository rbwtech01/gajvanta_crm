<?php

namespace App\Filament\Resources\MachineModels\Pages;

use App\Filament\Resources\MachineModels\MachineModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMachineModels extends ListRecords
{
    protected static string $resource = MachineModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
