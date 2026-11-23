<?php

namespace App\Filament\Resources\CallCenters\Pages;

use App\Filament\Resources\CallCenters\CallCenterResource;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCallCenter extends EditRecord
{
    protected static string $resource = CallCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
