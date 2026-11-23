<?php

namespace App\Filament\Resources\MachineModelAttachments\Pages;

use App\Filament\Resources\MachineModelAttachments\MachineModelAttachmentResource;
use Filament\Resources\Pages\EditRecord;

class EditMachineModelAttachment extends EditRecord
{
    protected static string $resource = MachineModelAttachmentResource::class;

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
