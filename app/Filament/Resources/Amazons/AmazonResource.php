<?php

namespace App\Filament\Resources\Amazons;

use App\Filament\Resources\Amazons\Pages\CreateAmazon;
use App\Filament\Resources\Amazons\Pages\EditAmazon;
use App\Filament\Resources\Amazons\Pages\ListAmazons;
use App\Filament\Resources\Amazons\Schemas\AmazonForm;
use App\Filament\Resources\Amazons\Tables\AmazonsTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AmazonResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationLabel = 'Amazon Orders';

    protected static ?int $navigationSort = 999;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AmazonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmazonsTable::configure($table);
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
            'index' => ListAmazons::route('/'),
            'create' => CreateAmazon::route('/create'),
            'edit' => EditAmazon::route('/{record}/edit'),
        ];
    }
}
