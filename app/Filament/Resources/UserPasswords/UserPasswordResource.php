<?php

namespace App\Filament\Resources\UserPasswords;

use App\Filament\Resources\UserPasswords\Pages\ListUserPasswords;
use App\Filament\Resources\UserPasswords\Schemas\UserPasswordForm;
use App\Filament\Resources\UserPasswords\Tables\UserPasswordsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserPasswordResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Password Manager';

    protected static ?int $navigationSort = 998;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserPasswordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserPasswordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserPasswords::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query): void {
                $query
                    ->whereDoesntHave('roles', function (Builder $query): void {
                        $query->where('name', 'Agent');
                    })
                    ->orWhere(function (Builder $query): void {
                        $query
                            ->where('is_active', true)
                            ->where('name', '!=', 'Amazon')
                            ->whereDoesntHave('callCenter', function (Builder $query): void {
                                $query->where('name', 'Amazon Store');
                            })
                            ->whereHas('roles', function (Builder $query): void {
                                $query->where('name', 'Agent');
                            });
                    });
            });
    }
}