<?php

namespace App\Filament\Manager\Resources\Agents;

use App\Filament\Manager\Resources\Agents\Pages\CreateAgent;
use App\Filament\Manager\Resources\Agents\Pages\EditAgent;
use App\Filament\Manager\Resources\Agents\Pages\ListAgents;
use App\Filament\Manager\Resources\Agents\Schemas\AgentForm;
use App\Filament\Manager\Resources\Agents\Tables\AgentsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AgentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Agent';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AgentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentsTable::configure($table);
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
            'index' => ListAgents::route('/'),
            'create' => CreateAgent::route('/create'),
            'edit' => EditAgent::route('/{record}/edit'),
            'customers' => Pages\ManageAgentCustomers::route('/{record}/customers'),
        ];
    }
}
