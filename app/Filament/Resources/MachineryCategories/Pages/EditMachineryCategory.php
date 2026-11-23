<?php

namespace App\Filament\Resources\MachineryCategories\Pages;

use App\Filament\Resources\MachineryCategories\MachineryCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditMachineryCategory extends EditRecord
{
    protected static string $resource = MachineryCategoryResource::class;

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
