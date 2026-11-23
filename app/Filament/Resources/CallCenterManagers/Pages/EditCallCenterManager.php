<?php

namespace App\Filament\Resources\CallCenterManagers\Pages;

use App\Filament\Resources\CallCenterManagers\CallCenterManagerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCallCenterManager extends EditRecord
{
    protected static string $resource = CallCenterManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
