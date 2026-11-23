<?php

namespace App\Filament\Resources\MachineModelAttachments\Pages;

use App\Filament\Resources\MachineModelAttachments\MachineModelAttachmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMachineModelAttachments extends ListRecords
{
    protected static string $resource = MachineModelAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
