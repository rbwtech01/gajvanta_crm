<?php

namespace App\Filament\Resources\CallCenterManagers\Pages;

use App\Filament\Resources\CallCenterManagers\CallCenterManagerResource;
use App\Notifications\AgentInvitationNotification;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateCallCenterManager extends CreateRecord
{
    protected static string $resource = CallCenterManagerResource::class;

    protected string $generatedPassword;

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->generatedPassword = $this->generateTemporaryPassword();

        // Login password
        $data['password'] = Hash::make($this->generatedPassword);

        // Plain password (encrypted automatically by model cast)
        $data['admin_password'] = $this->generatedPassword;

        $data['created_by'] = auth()->id();

        return $data;
    }

    private function generateTemporaryPassword(): string
    {
        $uppercase = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $lowercase = str_split('abcdefghijklmnopqrstuvwxyz');
        $special = ['@', '#'];
        $pool = array_merge($uppercase, $lowercase, $special);

        $password = [
            $uppercase[array_rand($uppercase)],
            $lowercase[array_rand($lowercase)],
            $special[array_rand($special)],
        ];

        while (count($password) < 8) {
            $password[] = $pool[array_rand($pool)];
        }

        shuffle($password);

        return implode($password);
    }

    protected function afterCreate(): void
    {
        $this->record->assignRole('Call Center Admin');

        $this->record->notify(
            new AgentInvitationNotification(
                temporaryPassword: $this->generatedPassword
            )
        );
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Temporary password created')
            ->body('Password: ' . $this->generatedPassword)
            ->success()
            ->persistent();
    }
}