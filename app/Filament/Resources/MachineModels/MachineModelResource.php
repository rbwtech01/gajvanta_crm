<?php

namespace App\Filament\Resources\MachineModels;

use App\Filament\Resources\MachineModels\Pages\CreateMachineModel;
use App\Filament\Resources\MachineModels\Pages\EditMachineModel;
use App\Filament\Resources\MachineModels\Pages\ListMachineModels;
use App\Filament\Resources\MachineModels\Schemas\MachineModelForm;
use App\Filament\Resources\MachineModels\Tables\MachineModelsTable;
use App\Models\MachineModel;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MachineModelResource extends Resource
{
    protected static ?string $model = MachineModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return MachineModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MachineModelsTable::configure($table);
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
            'index' => ListMachineModels::route('/'),
            'create' => CreateMachineModel::route('/create'),
            'edit' => EditMachineModel::route('/{record}/edit'),
        ];
    }
}
