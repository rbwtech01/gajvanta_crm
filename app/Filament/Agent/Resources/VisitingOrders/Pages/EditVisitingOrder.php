<?php

namespace App\Filament\Agent\Resources\VisitingOrders\Pages;

use App\Filament\Agent\Resources\VisitingOrders\VisitingOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditVisitingOrder extends EditRecord
{
    protected static string $resource = VisitingOrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return [
            'visit_date' => $data['visit_date'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}