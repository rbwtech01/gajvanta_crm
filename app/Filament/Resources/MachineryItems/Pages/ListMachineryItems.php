<?php

namespace App\Filament\Resources\MachineryItems\Pages;

use App\Filament\Resources\MachineryItems\MachineryItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMachineryItems extends ListRecords
{
    protected static string $resource = MachineryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
