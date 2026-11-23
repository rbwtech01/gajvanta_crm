<?php

namespace App\Filament\Resources\Amazons\Pages;

use App\Filament\Resources\Amazons\AmazonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAmazon extends EditRecord
{
    protected static string $resource = AmazonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
