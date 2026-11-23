<?php

namespace App\Filament\Resources\MachineryItems\Pages;

use App\Filament\Resources\MachineryItems\MachineryItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMachineryItem extends CreateRecord
{
    protected static string $resource = MachineryItemResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
