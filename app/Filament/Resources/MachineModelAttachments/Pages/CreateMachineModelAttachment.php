<?php

namespace App\Filament\Resources\MachineModelAttachments\Pages;

use App\Filament\Resources\MachineModelAttachments\MachineModelAttachmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMachineModelAttachment extends CreateRecord
{
    protected static string $resource = MachineModelAttachmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected static bool $canCreateAnother = false;
}
