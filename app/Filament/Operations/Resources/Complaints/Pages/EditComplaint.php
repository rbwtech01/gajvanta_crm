<?php

namespace App\Filament\Operations\Resources\Complaints\Pages;

use App\Filament\Operations\Resources\Complaints\ComplaintResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComplaint extends EditRecord
{
    protected static string $resource = ComplaintResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['reply'] ?? null)) {
            $data['status'] = 'closed';
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}