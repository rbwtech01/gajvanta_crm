<?php

namespace App\Filament\Resources\MachineryCategories;

use App\Filament\Resources\MachineryCategories\Pages\CreateMachineryCategory;
use App\Filament\Resources\MachineryCategories\Pages\EditMachineryCategory;
use App\Filament\Resources\MachineryCategories\Pages\ListMachineryCategories;
use App\Filament\Resources\MachineryCategories\Schemas\MachineryCategoryForm;
use App\Filament\Resources\MachineryCategories\Tables\MachineryCategoriesTable;
use App\Models\MachineryCategory;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MachineryCategoryResource extends Resource
{
    protected static ?string $model = MachineryCategory::class;

    protected static string|UnitEnum|null $navigationGroup = 'Machinery';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MachineryCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MachineryCategoriesTable::configure($table);
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
            'index' => ListMachineryCategories::route('/'),
            'create' => CreateMachineryCategory::route('/create'),
            'edit' => EditMachineryCategory::route('/{record}/edit'),
        ];
    }
}
