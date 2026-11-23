<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Notifications\AgentInvitationNotification;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

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

        // Admin ke liye (auto encrypted because of model cast)
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
        $this->record->assignRole('Agent');

        $this->record->notify(new AgentInvitationNotification(temporaryPassword: $this->generatedPassword));
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
