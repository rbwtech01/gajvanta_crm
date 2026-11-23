<?php

namespace App\Filament\Resources\MachineryItems\Pages;

use App\Filament\Resources\MachineryItems\MachineryItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMachineryItem extends EditRecord
{
    protected static string $resource = MachineryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
