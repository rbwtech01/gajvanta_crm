<?php

namespace App\Filament\Agent\Resources\VisitingOrders\Pages;

use App\Filament\Agent\Resources\VisitingOrders\VisitingOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVisitingOrders extends ListRecords
{
    protected static string $resource = VisitingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
