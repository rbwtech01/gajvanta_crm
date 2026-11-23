<?php

namespace App\Filament\Resources\Amazons\Pages;

use App\Filament\Resources\Amazons\AmazonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAmazons extends ListRecords
{
    protected static string $resource = AmazonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
