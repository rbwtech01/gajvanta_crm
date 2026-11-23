<?php

namespace App\Filament\Resources\VisitingOrders\Pages;

use App\Filament\Resources\VisitingOrders\VisitingOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVisitingOrder extends EditRecord
{
    protected static string $resource = VisitingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
