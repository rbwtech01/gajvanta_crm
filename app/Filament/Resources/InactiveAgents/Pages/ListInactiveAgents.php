<?php

namespace App\Filament\Resources\InactiveAgents\Pages;

use App\Filament\Resources\InactiveAgents\InactiveAgentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInactiveAgents extends ListRecords
{
    protected static string $resource = InactiveAgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
