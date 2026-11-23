<?php

namespace App\Filament\Resources\InactiveAgents\Pages;

use App\Filament\Resources\InactiveAgents\InactiveAgentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInactiveAgent extends EditRecord
{
    protected static string $resource = InactiveAgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
