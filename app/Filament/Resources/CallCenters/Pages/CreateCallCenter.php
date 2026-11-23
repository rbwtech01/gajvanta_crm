<?php

namespace App\Filament\Resources\CallCenters\Pages;

use App\Filament\Resources\CallCenters\CallCenterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCallCenter extends CreateRecord
{
    protected static string $resource = CallCenterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function canCreateAnother(): bool
    {
        return false;
    }
}
