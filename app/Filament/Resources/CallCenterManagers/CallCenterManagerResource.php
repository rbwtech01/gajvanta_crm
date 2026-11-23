<?php

namespace App\Filament\Resources\CallCenterManagers;

use App\Filament\Resources\CallCenterManagers\Pages\CreateCallCenterManager;
use App\Filament\Resources\CallCenterManagers\Pages\EditCallCenterManager;
use App\Filament\Resources\CallCenterManagers\Pages\ListCallCenterManagers;
use App\Filament\Resources\CallCenterManagers\Schemas\CallCenterManagerForm;
use App\Filament\Resources\CallCenterManagers\Tables\CallCenterManagersTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CallCenterManagerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Call Center Managers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Call Center Manager';

    protected static ?string $pluralModelLabel = 'Call Center Managers';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return CallCenterManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CallCenterManagersTable::configure($table);
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
            'index' => ListCallCenterManagers::route('/'),
            'create' => CreateCallCenterManager::route('/create'),
            'edit' => EditCallCenterManager::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->role('Call Center Admin');
    }
}
