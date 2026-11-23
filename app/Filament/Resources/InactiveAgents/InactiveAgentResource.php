<?php

namespace App\Filament\Resources\InactiveAgents;

use App\Filament\Resources\InactiveAgents\Pages\CreateInactiveAgent;
use App\Filament\Resources\InactiveAgents\Pages\EditInactiveAgent;
use App\Filament\Resources\InactiveAgents\Pages\ListInactiveAgents;
use App\Filament\Resources\InactiveAgents\Schemas\InactiveAgentForm;
use App\Filament\Resources\InactiveAgents\Tables\InactiveAgentsTable;
use App\Models\User;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InactiveAgentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    protected static ?string $recordTitleAttribute = 'User';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Inactive Agent';

    protected static ?string $pluralModelLabel = 'Inactive Agents';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_active', 0);
    }

    public static function form(Schema $schema): Schema
    {
        return InactiveAgentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InactiveAgentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInactiveAgents::route('/'),
            'create' => CreateInactiveAgent::route('/create'),
            'edit' => EditInactiveAgent::route('/{record}/edit'),
        ];
    }
}
