<?php

namespace App\Filament\Resources\CallCenterManagers\Pages;

use App\Filament\Resources\CallCenterManagers\CallCenterManagerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCallCenterManagers extends ListRecords
{
    protected static string $resource = CallCenterManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
