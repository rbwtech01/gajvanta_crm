<?php

namespace App\Filament\Shipment\Resources\Customers\Pages;

use App\Filament\Shipment\Resources\Customers\CustomerResource;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $customer = $this->record->fresh(['shipment']);

        $shipment = $customer->shipment;

        if (
            $shipment?->tracking_id &&
            ! in_array($customer->status, ['dispatched', 'delivered'])
        ) {
            // Customer status
            $customer->status = 'dispatched';
            $customer->save();

            // Shipment dispatch date
            $shipment->dispatch_date = now()->addDay()->toDateString();
            $shipment->save();
        }
    }
}