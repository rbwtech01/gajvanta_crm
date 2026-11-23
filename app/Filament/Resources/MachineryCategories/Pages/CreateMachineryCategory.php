<?php

namespace App\Filament\Resources\MachineryCategories\Pages;

use App\Filament\Resources\MachineryCategories\MachineryCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMachineryCategory extends CreateRecord
{
    protected static string $resource = MachineryCategoryResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
