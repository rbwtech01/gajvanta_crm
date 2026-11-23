<?php

namespace App\Filament\Resources\MachineryItems;

use App\Filament\Resources\MachineryItems\Pages\CreateMachineryItem;
use App\Filament\Resources\MachineryItems\Pages\EditMachineryItem;
use App\Filament\Resources\MachineryItems\Pages\ListMachineryItems;
use App\Filament\Resources\MachineryItems\RelationManagers\SerialsRelationManager;
use App\Filament\Resources\MachineryItems\Schemas\MachineryItemForm;
use App\Filament\Resources\MachineryItems\Tables\MachineryItemsTable;
use App\Models\MachineryItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use App\Filament\Resources\MachineryItems\RelationManagers;

class MachineryItemResource extends Resource
{
    protected static ?string $model = MachineryItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Machinery';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MachineryItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MachineryItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SerialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMachineryItems::route('/'),
            'create' => CreateMachineryItem::route('/create'),
            'edit' => EditMachineryItem::route('/{record}/edit'),
        ];
    }
}
