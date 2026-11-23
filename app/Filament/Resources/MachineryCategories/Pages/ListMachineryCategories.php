<?php

namespace App\Filament\Resources\MachineryCategories\Pages;

use App\Filament\Resources\MachineryCategories\MachineryCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMachineryCategories extends ListRecords
{
    protected static string $resource = MachineryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
