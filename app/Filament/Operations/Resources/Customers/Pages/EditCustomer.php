<?php

namespace App\Filament\Operations\Resources\Customers\Pages;

use App\Filament\Operations\Resources\Customers\CustomerResource;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'processing') {
            $data['hold_reason_id'] = null;
            $data['hold_remark'] = null;
        }

        // If Bill Number is filled, automatically confirm the order
        if (filled($data['bill_number'] ?? null)) {
            $data['status'] = 'confirmed';
        }

        return $data;
    }

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
    
}