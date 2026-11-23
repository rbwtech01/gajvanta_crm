<?php

namespace App\Filament\Resources\CallCenters;

use App\Filament\Resources\CallCenters\Pages\CreateCallCenter;
use App\Filament\Resources\CallCenters\Pages\EditCallCenter;
use App\Filament\Resources\CallCenters\Pages\ListCallCenters;
use App\Filament\Resources\CallCenters\Schemas\CallCenterForm;
use App\Filament\Resources\CallCenters\Tables\CallCentersTable;
use App\Models\CallCenter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CallCenterResource extends Resource
{
    protected static ?string $model = CallCenter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CallCenterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CallCentersTable::configure($table);
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
            'index' => ListCallCenters::route('/'),
            'create' => CreateCallCenter::route('/create'),
            'edit' => EditCallCenter::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
